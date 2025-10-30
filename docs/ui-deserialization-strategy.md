# Estrategia de Reconstrucción de UI desde JSON Cache

**Fecha:** 30 de octubre de 2025  
**Branch:** cache-management-aisolated  
**Estado:** Diseño aprobado, pendiente de implementación

---

## Problema

Cuando un servicio de UI necesita procesar un evento, debe reconstruir el árbol completo de componentes desde el JSON cacheado para poder manipularlos como objetos. El JSON almacenado es una **estructura plana** (diccionario) donde:

- **Clave:** ID numérico del componente
- **Valor:** Configuración del componente (incluyendo `type`, `parent`, `_order`, propiedades)
- **Relaciones:** Cada componente tiene una propiedad `parent` que apunta al ID de su padre

### Estructura JSON Ejemplo:
```json
{
  "12345": {
    "type": "container",
    "name": "main",
    "layout": "vertical",
    "_id": 12345,
    "_order": 1,
    "parent": "main"
  },
  "67890": {
    "type": "table",
    "name": "my_table",
    "_id": 67890,
    "_order": 2,
    "parent": 12345,
    "rows": 3,
    "cols": 5
  },
  "11111": {
    "type": "tableheaderrow",
    "name": "header",
    "_id": 11111,
    "_order": 1,
    "parent": 67890
  }
}
```

### Desafíos:

1. **Componentes desordenados** - El JSON no tiene un orden jerárquico garantizado
2. **Relaciones padre-hijo** - Cada componente solo conoce a su padre, no a sus hijos
3. **Componentes especializados** - Las tablas tienen estructuras complejas (header row, rows container, cells)
4. **Constructores con dependencias** - Algunos componentes requieren referencias a sus padres en el constructor (ej: `TableRowBuilder` necesita `TableBuilder`)
5. **Encapsulamiento** - Cada tipo de padre tiene su propia forma de agregar hijos (ej: tabla usa `addRow()`, container usa `add()`)

---

## Solución: Patrón "Ask Parent to Add Me"

### Principios de Diseño:

1. **Responsabilidad única** - Cada componente sabe cómo deserializarse a sí mismo
2. **Encapsulamiento** - El padre decide cómo agregar a sus hijos
3. **Dos fases separadas** - Instanciación primero, ensamblaje después
4. **Complejidad O(n)** - Cada componente se procesa una vez

---

## Arquitectura de Tres Fases

### **Fase 1: Instanciación**

Crear todos los componentes con sus propiedades básicas, **sin resolver relaciones**.

#### Interface:
```php
interface JsonDeserializable {
    /**
     * Create component instance from JSON data
     * WITHOUT resolving parent-child relationships
     * 
     * @param array $data Component JSON configuration
     * @return self Component instance with basic properties
     */
    public static function instantiate(array $data): self;
}
```

#### Responsabilidades:
- Crear la instancia del componente
- Restaurar el ID original usando reflection
- Aplicar propiedades básicas (text, style, etc.)
- **NO** intentar resolver relaciones con otros componentes

#### Ejemplo - LabelBuilder:
```php
public static function instantiate(array $data): self {
    $label = new self($data['name'] ?? 'label');
    
    // Restore original ID
    $reflection = new ReflectionProperty(self::class, 'id');
    $reflection->setValue($label, $data['_id']);
    
    // Restore properties
    if (isset($data['text'])) {
        $label->text($data['text']);
    }
    if (isset($data['style'])) {
        $label->style($data['style']);
    }
    
    return $label;
}
```

#### Ejemplo - TableBuilder:
```php
public static function instantiate(array $data): self {
    $table = new self($data['name'] ?? 'table');
    
    // Restore ID
    $reflection = new ReflectionProperty(self::class, 'id');
    $reflection->setValue($table, $data['_id']);
    
    // Restore basic properties
    if (isset($data['rows']) && isset($data['cols'])) {
        $table->dimensions($data['rows'], $data['cols']);
    }
    if (isset($data['striped'])) {
        $table->striped($data['striped']);
    }
    
    // NO restaurar headerRow ni rows aquí
    // Eso se hace en Fase 2
    
    return $table;
}
```

---

### **Fase 2: Ensamblaje (Self-Registration)**

Cada hijo busca a su padre y le pide que lo agregue.

#### Interface:
```php
interface JsonDeserializable {
    /**
     * Find parent component and ask it to add this component
     * 
     * @param array $allComponents Map of ID => Component instances
     * @param array $jsonData Original JSON data for all components
     * @return void
     */
    public function attachToParent(array $allComponents, array $jsonData): void;
    
    /**
     * Add a child component to this component
     * Each parent type implements its own logic
     * 
     * @param mixed $child The child component to add
     * @param array $childData The child's JSON data (for context)
     * @return void
     */
    public function addChild(mixed $child, array $childData): void;
}
```

#### Responsabilidades del HIJO (`attachToParent`):
1. Obtener su propio ID
2. Buscar `parent` en el JSON
3. Encontrar la instancia del padre en `$allComponents`
4. Pedirle al padre que lo agregue llamando `$parent->addChild($this, $myData)`

#### Responsabilidades del PADRE (`addChild`):
1. Determinar el tipo del hijo
2. Decidir si es un hijo válido
3. Agregarlo usando su método interno apropiado
4. Actualizar propiedades internas si es necesario

#### Ejemplo - TableRowBuilder (hijo):
```php
public function attachToParent(array $allComponents, array $jsonData): void {
    $myId = $this->getId();
    $parentId = $jsonData[$myId]['parent'] ?? null;
    
    if (!$parentId || !isset($allComponents[$parentId])) {
        return; // No parent found
    }
    
    $parent = $allComponents[$parentId];
    
    // Ask parent to add me
    if (method_exists($parent, 'addChild')) {
        $parent->addChild($this, $jsonData[$myId]);
    }
}
```

#### Ejemplo - TableBuilder (padre):
```php
public function addChild(mixed $child, array $childData): void {
    if ($child instanceof TableHeaderRowBuilder) {
        // Set header row
        $reflection = new ReflectionProperty(self::class, 'headerRow');
        $reflection->setValue($this, $child);
        
        // Update config
        $this->config['header_row'] = $child->getId();
        
    } elseif ($child instanceof TableRowBuilder) {
        // Add data row
        $this->addRow($child);
        
    } elseif ($child instanceof UIContainer && $child->getName() === 'rows') {
        // Restore rows container reference
        $reflection = new ReflectionProperty(self::class, 'rowsContainer');
        $reflection->setValue($this, $child);
    }
    
    // Ignore invalid children (silently)
}
```

#### Ejemplo - UIContainer (padre genérico):
```php
public function addChild(mixed $child, array $childData): void {
    // Container accepts any component
    if (method_exists($child, 'getId')) {
        $this->add($child);
    }
}
```

#### Ejemplo - TableHeaderRowBuilder (padre):
```php
public function addChild(mixed $child, array $childData): void {
    if ($child instanceof TableHeaderCellBuilder) {
        $this->addCell($child);
    }
    // Only accepts header cells
}
```

---

### **Fase 3: Post-Ensamblaje (Opcional)**

Para casos especiales que requieren procesamiento después de que todo el árbol está ensamblado.

#### Interface:
```php
interface JsonDeserializable {
    /**
     * Optional: Finalize component after full tree is assembled
     * Use for computations that require the complete hierarchy
     * 
     * @param array $allComponents Map of all components
     * @return void
     */
    public function finalizeAssembly(array $allComponents): void;
}
```

#### Ejemplo de uso:
- Calcular dimensiones basadas en hijos
- Validar que la estructura es correcta
- Establecer referencias bidireccionales

---

## Implementación en AbstractUIService

### Método Principal: `reconstructContainerFromJson()`

```php
protected function reconstructContainerFromJson(array $jsonUI): UIContainer {
    if (empty($jsonUI)) {
        return $this->buildBaseUI();
    }
    
    // ===== FASE 1: INSTANCIACIÓN =====
    $components = [];
    foreach ($jsonUI as $id => $data) {
        $component = $this->instantiateFromType($data);
        if ($component) {
            $components[$id] = $component;
        }
    }
    
    // ===== FASE 2: ENSAMBLAJE =====
    // Sort by _order to maintain correct insertion sequence
    $sortedIds = $this->sortComponentIdsByOrder($components, $jsonUI);
    
    foreach ($sortedIds as $id) {
        $component = $components[$id];
        if (method_exists($component, 'attachToParent')) {
            $component->attachToParent($components, $jsonUI);
        }
    }
    
    // ===== FASE 3: POST-ENSAMBLAJE (opcional) =====
    foreach ($components as $component) {
        if (method_exists($component, 'finalizeAssembly')) {
            $component->finalizeAssembly($components);
        }
    }
    
    // Return root container
    return $this->findRootContainer($components);
}
```

### Helper: `instantiateFromType()`

```php
protected function instantiateFromType(array $data): ?object {
    $type = $data['type'] ?? null;
    if (!$type) {
        return null;
    }
    
    $class = $this->getComponentClass($type);
    
    // Check if component implements JsonDeserializable
    if (method_exists($class, 'instantiate')) {
        return $class::instantiate($data);
    }
    
    // Fallback for components that don't implement the interface yet
    return $this->legacyInstantiate($data);
}
```

### Helper: `getComponentClass()`

```php
protected function getComponentClass(string $type): string {
    return match ($type) {
        'container' => UIContainer::class,
        'label' => LabelBuilder::class,
        'button' => ButtonBuilder::class,
        'input' => InputBuilder::class,
        'select' => SelectBuilder::class,
        'checkbox' => CheckboxBuilder::class,
        'table' => TableBuilder::class,
        'tableheaderrow' => TableHeaderRowBuilder::class,
        'tablerow' => TableRowBuilder::class,
        'tablecell' => TableCellBuilder::class,
        'tableheadercell' => TableHeaderCellBuilder::class,
        default => throw new RuntimeException("Unknown component type: {$type}")
    };
}
```

### Helper: `sortComponentIdsByOrder()`

```php
protected function sortComponentIdsByOrder(array $components, array $jsonUI): array {
    $ids = array_keys($components);
    
    usort($ids, function($a, $b) use ($jsonUI) {
        $orderA = $jsonUI[$a]['_order'] ?? PHP_INT_MAX;
        $orderB = $jsonUI[$b]['_order'] ?? PHP_INT_MAX;
        return $orderA <=> $orderB;
    });
    
    return $ids;
}
```

### Helper: `findRootContainer()`

```php
protected function findRootContainer(array $components): UIContainer {
    foreach ($components as $component) {
        if ($component instanceof UIContainer) {
            // Check if it's root (no parent or parent is string)
            // Root container has parent = 'main' or similar
            return $component;
        }
    }
    
    throw new RuntimeException("No root container found in components");
}
```

---

## Plan de Implementación

### Paso 1: Crear la Interface Base
**Archivo:** `app/Services/UI/Contracts/JsonDeserializable.php`

```php
<?php

namespace App\Services\UI\Contracts;

interface JsonDeserializable
{
    /**
     * Create component from JSON without resolving relationships
     */
    public static function instantiate(array $data): self;
    
    /**
     * Find parent and ask it to add this component
     */
    public function attachToParent(array $allComponents, array $jsonData): void;
    
    /**
     * Add a child component (called by children)
     */
    public function addChild(mixed $child, array $childData): void;
    
    /**
     * Optional: Finalize after full tree is assembled
     */
    public function finalizeAssembly(array $allComponents): void;
}
```

### Paso 2: Implementar en UIComponent (Base)
**Archivo:** `app/Services/UI/Components/UIComponent.php`

Agregar implementación por defecto:
```php
public function attachToParent(array $allComponents, array $jsonData): void {
    // Default implementation for simple components
    $myId = $this->getId();
    $parentId = $jsonData[$myId]['parent'] ?? null;
    
    if ($parentId && isset($allComponents[$parentId])) {
        $parent = $allComponents[$parentId];
        if (method_exists($parent, 'addChild')) {
            $parent->addChild($this, $jsonData[$myId]);
        }
    }
}

public function finalizeAssembly(array $allComponents): void {
    // Optional - override if needed
}
```

### Paso 3: Implementar en Componentes Simples
Componentes como `LabelBuilder`, `ButtonBuilder`, `InputBuilder`, etc.

Solo necesitan implementar `instantiate()`:
```php
public static function instantiate(array $data): self {
    $component = new self($data['name'] ?? 'component');
    
    // Restore ID
    $reflection = new ReflectionProperty(self::class, 'id');
    $reflection->setValue($component, $data['_id']);
    
    // Restore properties
    foreach ($data as $key => $value) {
        if (in_array($key, ['_id', '_order', 'type', 'name', 'parent'])) {
            continue;
        }
        if (method_exists($component, $key)) {
            try {
                $component->$key($value);
            } catch (\Exception $e) {
                // Ignore properties that can't be set
            }
        }
    }
    
    return $component;
}
```

### Paso 4: Implementar en UIContainer
**Archivo:** `app/Services/UI/Components/UIContainer.php`

```php
public static function instantiate(array $data): self {
    $container = new self($data['name'] ?? 'container');
    
    // Restore ID
    $reflection = new ReflectionProperty(self::class, 'id');
    $reflection->setValue($container, $data['_id']);
    
    // Restore properties
    if (isset($data['layout'])) {
        $container->layout(LayoutType::from($data['layout']));
    }
    if (isset($data['title'])) {
        $container->title($data['title']);
    }
    if (isset($data['parent'])) {
        $container->parent($data['parent']);
    }
    
    return $container;
}

public function addChild(mixed $child, array $childData): void {
    // Container accepts any component
    if (is_object($child) && method_exists($child, 'getId')) {
        $this->add($child);
    }
}
```

### Paso 5: Implementar en TableBuilder
**Archivo:** `app/Services/UI/Components/TableBuilder.php`

```php
public static function instantiate(array $data): self {
    $table = new self($data['name'] ?? 'table');
    
    // Restore ID
    $reflection = new ReflectionProperty(self::class, 'id');
    $reflection->setValue($table, $data['_id']);
    
    // Restore table-specific properties
    if (isset($data['rows']) && isset($data['cols'])) {
        $table->dimensions($data['rows'], $data['cols']);
    }
    if (isset($data['striped'])) {
        $table->striped($data['striped']);
    }
    
    return $table;
}

public function addChild(mixed $child, array $childData): void {
    if ($child instanceof TableHeaderRowBuilder) {
        // Inject header row
        $reflection = new ReflectionProperty(self::class, 'headerRow');
        $reflection->setValue($this, $child);
        $this->config['header_row'] = $child->getId();
        
    } elseif ($child instanceof TableRowBuilder) {
        $this->addRow($child);
        
    } elseif ($child instanceof UIContainer && 
              str_contains($child->getName(), 'rows')) {
        // Restore rows container
        $reflection = new ReflectionProperty(self::class, 'rowsContainer');
        $reflection->setValue($this, $child);
    }
}
```

### Paso 6: Implementar en TableRowBuilder
**Archivo:** `app/Services/UI/Components/TableRowBuilder.php`

```php
public static function instantiate(array $data): self {
    // TableRowBuilder requires TableBuilder in constructor
    // We'll create it with null and fix the reference later
    $row = new self(null, $data['name'] ?? 'row');
    
    // Restore ID
    $reflection = new ReflectionProperty(self::class, 'id');
    $reflection->setValue($row, $data['_id']);
    
    // Restore properties
    if (isset($data['min_height'])) {
        $row->minHeight($data['min_height']);
    }
    
    return $row;
}

public function attachToParent(array $allComponents, array $jsonData): void {
    $myId = $this->getId();
    $parentId = $jsonData[$myId]['parent'] ?? null;
    
    if ($parentId && isset($allComponents[$parentId])) {
        $parent = $allComponents[$parentId];
        
        // Fix table reference using reflection
        if ($parent instanceof TableBuilder) {
            $reflection = new ReflectionProperty(self::class, 'table');
            $reflection->setValue($this, $parent);
        }
        
        // Ask parent to add me
        if (method_exists($parent, 'addChild')) {
            $parent->addChild($this, $jsonData[$myId]);
        }
    }
}

public function addChild(mixed $child, array $childData): void {
    if ($child instanceof TableCellBuilder) {
        $this->addCell($child);
    }
}
```

### Paso 7: Implementar en TableCellBuilder
Similar a `TableRowBuilder`, con referencia al row padre.

### Paso 8: Actualizar AbstractUIService
Implementar los métodos helper descritos arriba.

---

## Ventajas de esta Arquitectura

✅ **Escalable** - Nuevos componentes solo implementan su lógica  
✅ **Mantenible** - La lógica está donde corresponde  
✅ **Type-safe** - Cada componente maneja sus propios tipos  
✅ **Eficiente** - O(n) complejidad  
✅ **Encapsulado** - El padre controla cómo agregar hijos  
✅ **Flexible** - Fácil agregar validaciones o transformaciones  
✅ **Testeable** - Cada fase se puede probar independientemente  

---

## Casos Especiales

### Container Rows de Tabla
El `TableBuilder` crea internamente un `UIContainer` para las filas. Este container también está en el JSON y debe ser restaurado:

```php
// En TableBuilder::addChild()
if ($child instanceof UIContainer && str_contains($child->getName(), 'rows')) {
    $reflection = new ReflectionProperty(self::class, 'rowsContainer');
    $reflection->setValue($this, $child);
}
```

### Componentes con Enums
Algunos componentes tienen propiedades que son enums (ej: `LayoutType`):

```php
if (isset($data['layout'])) {
    $container->layout(LayoutType::from($data['layout']));
}
```

### Propiedades Protegidas
Usar `ReflectionProperty` para acceder y modificar propiedades protegidas:

```php
$reflection = new ReflectionProperty(self::class, 'propertyName');
$reflection->setValue($object, $value);
```

---

## Testing

### Test de Fase 1 (Instanciación)
```php
test('can instantiate all component types from JSON', function() {
    $jsonData = [
        '_id' => 12345,
        'type' => 'label',
        'name' => 'test_label',
        'text' => 'Hello World'
    ];
    
    $label = LabelBuilder::instantiate($jsonData);
    
    expect($label->getId())->toBe(12345);
    expect($label->getName())->toBe('test_label');
});
```

### Test de Fase 2 (Ensamblaje)
```php
test('children attach to parent correctly', function() {
    $jsonUI = [
        100 => ['type' => 'container', '_id' => 100, 'name' => 'main'],
        101 => ['type' => 'label', '_id' => 101, 'name' => 'lbl', 'parent' => 100]
    ];
    
    $container = UIContainer::instantiate($jsonUI[100]);
    $label = LabelBuilder::instantiate($jsonUI[101]);
    
    $components = [100 => $container, 101 => $label];
    
    $label->attachToParent($components, $jsonUI);
    
    $children = $container->getChildren();
    expect($children)->toHaveCount(1);
    expect($children[101])->toBe($label);
});
```

### Test Completo
```php
test('full reconstruction from JSON', function() {
    $service = new TableDemoService();
    $service->clearStoredUI();
    
    // Build initial UI
    $json = $service->getUI();
    
    // Reconstruct container
    $service->initializeEventContext();
    
    // Verify structure
    $container = $service->getContainer();
    expect($container)->toBeInstanceOf(UIContainer::class);
    
    $table = $container->findByName('demo_table');
    expect($table)->toBeInstanceOf(TableBuilder::class);
    expect($table->getHeaderRow())->not->toBeNull();
});
```

---

## Próximos Pasos

1. ✅ Crear interface `JsonDeserializable`
2. ✅ Implementar en `UIComponent` (base)
3. ✅ Implementar en componentes simples (Label, Button, Input, etc.)
4. ✅ Implementar en `UIContainer`
5. ✅ Implementar en `TableBuilder` y componentes relacionados
6. ✅ Actualizar `AbstractUIService::reconstructContainerFromJson()`
7. ✅ Crear tests unitarios
8. ✅ Probar con `TableDemoService`
9. ✅ Documentar ejemplos adicionales

---

## Notas Adicionales

- **Orden de procesamiento:** Es importante procesar los componentes en orden por `_order` para mantener la secuencia de inserción correcta
- **Componentes huérfanos:** Si un componente no encuentra a su padre, simplemente no se agrega (silenciosamente)
- **Validación:** Cada padre puede validar si acepta o rechaza un tipo de hijo
- **Reflection:** Se usa extensivamente para acceder a propiedades protegidas durante la deserialización
- **Backward compatibility:** Los componentes que aún no implementan la interface pueden usar un método legacy de fallback

---

**Documento listo para continuar la implementación mañana.**
