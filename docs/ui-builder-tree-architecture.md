# UI Builder - Arquitectura de Árbol

## Resumen

El sistema UI Builder ha sido refactorizado para implementar el **Patrón Composite**, permitiendo construir interfaces de usuario como estructuras de árbol con métodos para manipular elementos dinámicamente.

## Arquitectura

### Componentes Principales

```
UIElement (interface)
├── UIComponent (abstract) - Elementos hoja
│   ├── ButtonBuilder
│   ├── LabelBuilder
│   ├── TableBuilder
│   └── [otros componentes]
└── UIContainer (composite) - Contenedor de elementos
```

### 1. UIElement (Interfaz)

Define el contrato para todos los elementos UI:

```php
interface UIElement
{
    public function getId(): string;
    public function getType(): string;
    public function toJson(): array;
    public function isVisible(): bool;
    public function setVisible(bool $visible): self;
}
```

### 2. UIComponent (Clase Abstracta)

Clase base para componentes hoja (Button, Label, Table, etc.):

```php
abstract class UIComponent implements UIElement
{
    protected string $id;
    protected string $type;
    protected array $config = [];
    
    abstract protected function getDefaultConfig(): array;
    
    public function toJson(): array
    {
        return [$this->id => $this->config];
    }
}
```

### 3. UIContainer (Composite)

Contenedor que puede almacenar y manipular elementos hijos:

```php
class UIContainer implements UIElement
{
    protected array $children = [];
    
    // Métodos de manipulación
    public function add(UIElement $element): self;
    public function remove(string $elementId): self;
    public function update(string $elementId, UIElement $newElement): self;
    public function find(string $elementId): ?UIElement;
    public function getChildren(): array;
    public function clear(): self;
    
    // Serialización recursiva
    public function toJson(): array;
}
```

## Ventajas de la Nueva Arquitectura

### ✅ Manipulación Dinámica

**Antes:**
```php
$elements = [];
$elements += UIBuilder::button('btn1')->build();
$elements += UIBuilder::button('btn2')->build();
// No se pueden modificar después
```

**Ahora:**
```php
$container = UIBuilder::container('parent')->getContainer();
$container->add(UIBuilder::button('btn1'));
$container->add(UIBuilder::button('btn2'));

// Modificar dinámicamente
$container->remove('btn1:button');
$container->update('btn2:button', UIBuilder::button('btn2')->label('Updated'));
```

### ✅ Estructura Jerárquica Clara

**Antes:**
```php
$elements = [];
$elements += UIBuilder::button('btn1')->build();
$elements += UIBuilder::label('lbl1')->build();
$elements += UIBuilder::container('actions')
    ->elements([/* elementos concatenados */])
    ->build();
```

**Ahora:**
```php
$container = UIBuilder::container('parent')->getContainer();

$container->add(UIBuilder::button('btn1'));
$container->add(UIBuilder::label('lbl1'));

$actions = new UIContainer('actions');
$actions->add(UIBuilder::button('play'));
$actions->add(UIBuilder::button('delete'));

$container->add($actions);
```

### ✅ Búsqueda de Elementos

```php
// Buscar en cualquier nivel del árbol
$element = $container->find('nested_button:button');

if ($element !== null) {
    // Modificar el elemento encontrado
    $container->update($element->getId(), $newElement);
}
```

### ✅ Serialización Recursiva

```php
// toJson() navega automáticamente todo el árbol
$json = $container->toJson();

// Resultado:
[
    'parent:container' => [
        'visible' => true,
        'layout' => 'vertical',
        'elements' => [
            'btn1:button' => [...],
            'lbl1:label' => [...],
            'actions:container' => [
                'elements' => [
                    'play:button' => [...],
                    'delete:button' => [...]
                ]
            ]
        ]
    ]
]
```

## Guía de Migración

### Ejemplo: GameLobbyScreenService

**ANTES (concatenación de arrays):**

```php
private function buildUIElements(bool $canCreateNewGame, int $maxInstances, array $saved_games): array
{
    $elements = [];

    // Concatenación con +=
    $elements += UIBuilder::button('new_game')
        ->label(t('new_game_button_label'))
        ->build();

    $elements += UIBuilder::label('warning_message')
        ->text(t('instances_limit_reached'))
        ->build();

    $elements += $this->buildSavedGamesTable($saved_games, $maxInstances);

    return $elements;
}
```

**AHORA (estructura de árbol):**

```php
private function buildUIElements($container, bool $canCreateNewGame, int $maxInstances, array $saved_games): void
{
    // Agregar elementos al contenedor
    $container->add(
        UIBuilder::button('new_game')
            ->label(t('new_game_button_label'))
    );

    $container->add(
        UIBuilder::label('warning_message')
            ->text(t('instances_limit_reached'))
    );

    $container->add(
        $this->buildSavedGamesTable($saved_games, $maxInstances)
    );
}
```

### Cambios Clave

1. **Retornar objetos en lugar de arrays:**
   - Builders ya no llaman `.build()` al agregar elementos
   - `.build()` solo se llama en el contenedor raíz al final

2. **Usar `add()` en lugar de `+=`:**
   - Los elementos se agregan al contenedor con `.add()`
   - Soporta elementos individuales o múltiples con `.addMany()`

3. **Constructores anidados:**
   ```php
   // Crear contenedor de acciones
   $actions = UIBuilder::container('actions')
       ->layout(LayoutType::HORIZONTAL);
   
   $actions->add(UIBuilder::button('play'));
   $actions->add(UIBuilder::button('delete'));
   
   // Agregar al contenedor principal
   $mainContainer->add($actions->getContainer());
   ```

## API Completa

### UIContainer

#### Métodos de Configuración
```php
->slot(string $slot): self
->layout(LayoutType $layout): self
->title(string $title): self
->visible(bool $visible): self
```

#### Métodos de Manipulación
```php
->add(UIElement $element): self
->addMany(array $elements): self
->remove(string $elementId): self
->tryRemove(string $elementId): bool
->update(string $elementId, UIElement $newElement): self
->clear(): self
```

#### Métodos de Consulta
```php
->find(string $elementId): ?UIElement
->has(string $elementId): bool
->getChildren(): array
->count(): int
```

#### Serialización
```php
->toJson(): array
->build(): array  // Alias de toJson()
```

### Componentes (Button, Label, Table)

Todos heredan de `UIComponent` y soportan:

```php
->getId(): string
->getType(): string
->isVisible(): bool
->setVisible(bool $visible): self
->visible(bool $visible): self
->toJson(): array
->build(): array  // Para retrocompatibilidad
```

## Ejemplos de Uso

### Ejemplo 1: UI Simple

```php
$container = UIBuilder::container('simple_ui')
    ->slot('canvas')
    ->title('Simple UI');

$container->add(
    UIBuilder::button('submit')
        ->label('Submit')
        ->action('submit_form')
        ->style('primary')
);

$container->add(
    UIBuilder::label('info')
        ->text('Please fill out the form')
        ->style('info')
);

return $container->build();
```

### Ejemplo 2: UI con Contenedores Anidados

```php
$root = UIBuilder::container('root')->getContainer();

// Header
$header = new UIContainer('header');
$header->add(UIBuilder::label('title')->text('My App'));
$root->add($header);

// Content
$content = new UIContainer('content');
$content->layout(LayoutType::VERTICAL);
$content->add(UIBuilder::button('btn1')->label('Action 1'));
$content->add(UIBuilder::button('btn2')->label('Action 2'));
$root->add($content);

// Footer
$footer = new UIContainer('footer');
$footer->add(UIBuilder::label('copyright')->text('© 2025'));
$root->add($footer);

return $root->toJson();
```

### Ejemplo 3: Modificación Dinámica

```php
$container = UIBuilder::container('dynamic')->getContainer();

// Agregar elementos iniciales
$container->add(UIBuilder::button('btn1')->label('Button 1'));
$container->add(UIBuilder::button('btn2')->label('Button 2'));
$container->add(UIBuilder::button('btn3')->label('Button 3'));

// Condicional: remover botón si no es necesario
if (!$needsButton2) {
    $container->remove('btn2:button');
}

// Actualizar botón existente
$container->update(
    'btn1:button',
    UIBuilder::button('btn1')->label('Updated Label')->enabled(false)
);

// Buscar y modificar elemento anidado
$nested = $container->find('nested_button:button');
if ($nested) {
    // Hacer algo con el elemento encontrado
}

return $container->toJson();
```

### Ejemplo 4: Tabla con Acciones

```php
$table = UIBuilder::table('games_table')
    ->title('Saved Games')
    ->addHeader('Name')
    ->addHeader('Status')
    ->addHeader('Actions');

$rows = [];
foreach ($games as $game) {
    $actions = UIBuilder::container("actions_{$game['id']}")
        ->layout(LayoutType::HORIZONTAL);
    
    $actions->add(
        UIBuilder::button("play_{$game['id']}")
            ->label('Play')
            ->icon('play')
            ->style('success')
    );
    
    $actions->add(
        UIBuilder::button("delete_{$game['id']}")
            ->label('Delete')
            ->icon('trash')
            ->style('danger')
    );
    
    $rows[] = [
        $game['name'],
        $game['status'],
        $actions->build()
    ];
}

$table->rows($rows);

return $table->build();
```

## Testing

Los tests unitarios cubren:

- ✅ Creación y configuración de contenedores
- ✅ Agregar, remover y actualizar elementos
- ✅ Búsqueda recursiva de elementos
- ✅ Validación de excepciones (IDs duplicados, elementos no encontrados)
- ✅ Serialización recursiva con `toJson()`
- ✅ Integración con ContainerBuilder
- ✅ Estructuras complejas anidadas

Ver: `tests/Unit/Services/UI/UIContainerTest.php`

Ejecutar tests:
```bash
./vendor/bin/pest tests/Unit/Services/UI/UIContainerTest.php
```

## Retrocompatibilidad

La nueva arquitectura mantiene compatibilidad con el código antiguo:

1. **ContainerBuilder** sigue existiendo y delega a `UIContainer`
2. **Método `.build()`** está disponible en todos los componentes
3. **Método `.elements()`** sigue funcionando (aunque marcado como deprecated)

### Migración Gradual

Puedes migrar gradualmente:

```php
// Código antiguo (sigue funcionando)
$ui = UIBuilder::container('test')
    ->elements([
        ...UIBuilder::button('btn1')->build(),
        ...UIBuilder::label('lbl1')->build(),
    ])
    ->build();

// Código nuevo (recomendado)
$container = UIBuilder::container('test');
$container->add(UIBuilder::button('btn1'));
$container->add(UIBuilder::label('lbl1'));
$ui = $container->build();
```

## Conclusión

La nueva arquitectura proporciona:

1. 🏗️ **Estructura clara:** Árbol jerárquico que refleja la UI
2. 🔧 **Manipulación flexible:** Agregar, remover, actualizar elementos dinámicamente
3. 🔍 **Búsqueda eficiente:** Encontrar elementos en cualquier nivel del árbol
4. 📦 **Serialización automática:** `toJson()` recursivo
5. ✅ **Type-safe:** Interfaces y clases bien definidas
6. 🧪 **Testeable:** Fácil de validar con tests unitarios
7. 🔄 **Retrocompatible:** Migración gradual sin romper código existente

Para más información, consulta:
- `app/Services/UI/Contracts/UIElement.php`
- `app/Services/UI/Components/UIComponent.php`
- `app/Services/UI/Components/UIContainer.php`
- `tests/Unit/Services/UI/UIContainerTest.php`
