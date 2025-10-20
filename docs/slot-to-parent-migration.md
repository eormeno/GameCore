# Cambio Global: `slot` → `parent`

**Fecha:** 20 de octubre de 2025  
**Branch:** ui-builder  
**Estado:** ✅ Completado

## Resumen

Se ha realizado un cambio terminológico global en todo el sistema UI, reemplazando el concepto de `slot` por `parent` para indicar la relación jerárquica entre componentes. Este cambio hace que la arquitectura sea más clara y semánticamente correcta.

## Motivación

El término "slot" sugería una posición o ranura donde se coloca un componente, lo cual no reflejaba correctamente la naturaleza de la relación padre-hijo en el árbol de componentes. El término `parent` es más explícito y describe mejor que cada componente conoce quién es su padre en la jerarquía.

## Cambios Realizados

### 1. Interfaz UIElement

**Archivo:** `app/Services/UI/Contracts/UIElement.php`

**Antes:**
```php
public function getSlot(): int|string|null;
public function setSlot(int|string|null $slot): self;
```

**Después:**
```php
public function getParent(): int|string|null;
public function setParent(int|string|null $parent): self;
```

### 2. Clase UIComponent

**Archivo:** `app/Services/UI/Components/UIComponent.php`

**Cambios:**
- Propiedad: `$slot` → `$parent`
- Métodos: 
  - `getSlot()` → `getParent()`
  - `setSlot()` → `setParent()`
  - `slot()` → `parent()` (fluent API)
- Config: `'slot' => null` → `'parent' => null`

### 3. Clase UIContainer

**Archivo:** `app/Services/UI/Components/UIContainer.php`

**Cambios:**
- Propiedad: `$slot` → `$parent`
- Métodos:
  - `getSlot()` → `getParent()`
  - `setSlot()` → `setParent()`
  - `parent()` (fluent API)
- Config: `'slot' => null` → `'parent' => null`
- En método `add()`: `$element->setSlot($this->id)` → `$element->setParent($this->id)`
- En métodos `remove()` y `tryRemove()`: `setSlot(null)` → `setParent(null)`
- Comentario en `toJson()`: "with 'slot' indicating..." → "with 'parent' indicating..."

### 4. Componentes Table

**Archivos modificados:**
- `TableBuilder.php`
- `TableRowBuilder.php`
- `TableCellBuilder.php`
- `TableHeaderRowBuilder.php`
- `TableHeaderCellBuilder.php` (no requirió cambios directos)

**Cambios:**
- Todas las llamadas `setSlot()` → `setParent()`
- Comentarios actualizados de "slot" a "parent"

### 5. Tests

**Archivos:** `tests/Unit/Services/UI/*.php`

**Cambios:**
- UIContainerTest.php:
  - Método `slot()` → `parent()`
  - Aserciones: `$config['slot']` → `$config['parent']`
  - Comentarios actualizados
- TableHeaderTest.php:
  - Nombres de tests: `"slot reference"` → `"parent reference"`
  - Aserciones: `['slot']` → `['parent']`

### 6. Archivos de Prueba

**Archivos:** `test_*.php` (raíz del proyecto)

**Cambios:**
- `test_table_cell.php`:
  - Verificaciones: `$compData['slot']` → `$compData['parent']`
  - Mensajes: `"slot reference"` → `"parent reference"`
- Otros archivos de test actualizados según corresponda

### 7. Documentación

**Archivos:** `docs/*.md`

**Cambios aplicados globalmente con sed:**
```bash
sed -i 's/"slot":/"parent":/g' *.md
sed -i 's/slot:/parent:/g' *.md
sed -i 's/setSlot/setParent/g' *.md
sed -i 's/getSlot/getParent/g' *.md
```

**Archivos de documentación actualizados:**
- table-header-refactoring.md
- session-tablecell-implementation.md
- SLOT_SYSTEM_*.md
- null-filtering.md
- Y todos los demás archivos .md en docs/

## Estructura JSON Antes y Después

### Antes (con slot)

```json
{
  "1": {
    "type": "table",
    "header_row": 3,
    "rows_container": 2
  },
  "2": {
    "type": "container",
    "slot": 1
  },
  "3": {
    "type": "tableheaderrow",
    "slot": 1
  },
  "4": {
    "type": "tableheadercell",
    "text": "Name",
    "slot": 3
  }
}
```

### Después (con parent)

```json
{
  "1": {
    "type": "table",
    "header_row": 3,
    "rows_container": 2
  },
  "2": {
    "type": "container",
    "parent": 1
  },
  "3": {
    "type": "tableheaderrow",
    "parent": 1
  },
  "4": {
    "type": "tableheadercell",
    "text": "Name",
    "parent": 3
  }
}
```

## API de Uso

### Antes

```php
// UIComponent/UIContainer
$component->setSlot(123);
$component->slot('canvas');
$parentId = $component->getSlot();

// UIContainer automático
$container->add($element); // Internamente: $element->setSlot($this->id)
```

### Después

```php
// UIComponent/UIContainer
$component->setParent(123);
$component->parent('canvas');
$parentId = $component->getParent();

// UIContainer automático
$container->add($element); // Internamente: $element->setParent($this->id)
```

## Verificación

### Tests Ejecutados

```bash
./vendor/bin/pest tests/Unit/Services/UI/ --compact
```

**Resultado:**
```
Tests:    1 skipped, 71 passed (222 assertions)
Duration: 0.48s
```

✅ **Todos los tests pasan correctamente**

### Archivos de Demostración

```bash
php test_new_header_system.php
php test_table_cell.php
```

✅ **Todos los tests de demostración pasan con las nuevas referencias parent**

### Errores de Compilación

```bash
# Verificación con get_errors
```

✅ **No hay errores de compilación**

## Impacto

### Breaking Changes

⚠️ **Este es un BREAKING CHANGE**

Cualquier código cliente que:
1. Acceda directamente a la propiedad `slot` en el JSON
2. Use los métodos `getSlot()` o `setSlot()`
3. Lea la documentación que mencione "slot"

Debe actualizarse para usar `parent` en su lugar.

### Compatibilidad Hacia Atrás

❌ **No hay compatibilidad hacia atrás**

No se mantuvieron los métodos antiguos como `@deprecated` porque este es un cambio fundamental en la nomenclatura del sistema.

### Migraciones Necesarias

**Frontend/Cliente:**
```javascript
// Antes
const parentId = component.slot;

// Después
const parentId = component.parent;
```

**Backend (servicios externos):**
```php
// Antes
$element->setSlot($parentId);
$parentId = $element->getSlot();

// Después
$element->setParent($parentId);
$parentId = $element->getParent();
```

## Archivos Modificados

### Código Fuente (11 archivos)
1. `app/Services/UI/Contracts/UIElement.php`
2. `app/Services/UI/Components/UIComponent.php`
3. `app/Services/UI/Components/UIContainer.php`
4. `app/Services/UI/Components/TableBuilder.php`
5. `app/Services/UI/Components/TableRowBuilder.php`
6. `app/Services/UI/Components/TableCellBuilder.php`
7. `app/Services/UI/Components/TableHeaderRowBuilder.php`

### Tests (2 archivos)
8. `tests/Unit/Services/UI/UIContainerTest.php`
9. `tests/Unit/Services/UI/TableHeaderTest.php`

### Archivos de Prueba (2 archivos)
10. `test_table_cell.php`
11. `test_new_header_system.php` (ya usaba el nuevo sistema)

### Documentación (34 archivos)
- Todos los archivos `.md` en `docs/` actualizados globalmente

## Beneficios del Cambio

1. **Claridad Semántica**: El término "parent" es más claro que "slot" para indicar relaciones jerárquicas
2. **Consistencia Conceptual**: Alineado con terminología estándar de árboles y estructuras de datos
3. **Mejor Comprensión**: Más fácil de entender para nuevos desarrolladores
4. **Documentación Clara**: Los diagramas y ejemplos son más intuitivos
5. **Menos Ambigüedad**: "slot" podía confundirse con posiciones o índices

## Nomenclatura Final

| Concepto | Término | Tipo | Descripción |
|----------|---------|------|-------------|
| Identificador único | `id` | int | ID numérico auto-incremental |
| Nombre semántico | `name` | string\|null | Nombre opcional para referencia |
| Referencia al padre | `parent` | int\|string\|null | ID del componente padre en la jerarquía |
| Tipo de componente | `type` | string | button, label, table, container, etc. |

## Conclusión

El cambio de `slot` a `parent` ha sido completado exitosamente en todo el sistema:
- ✅ Código fuente actualizado
- ✅ Tests actualizados y pasando
- ✅ Documentación actualizada
- ✅ Sin errores de compilación
- ✅ Archivos de demostración funcionando

El sistema UI ahora usa una terminología más clara y consistente para las relaciones jerárquicas entre componentes.
