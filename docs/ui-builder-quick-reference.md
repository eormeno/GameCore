# 🚀 Quick Reference: UI Builder

## Sistema de Identificación

```php
// ID Técnico (auto-generado)
$container->getId();  // 32600001

// Name Semántico (manual)
UIBuilder::container('game_lobby_screen')  // name = "game_lobby_screen"
```

## Construcción Básica

```php
// Container
$screen = UIBuilder::container('main_screen')
    ->slot('canvas')              // Dónde renderizar
    ->layout(LayoutType::VERTICAL)
    ->title('My Screen');

// Button
UIBuilder::button('submit_btn')
    ->label('Submit')
    ->action('submit_form')
    ->style('primary')
    ->enabled(true);

// Label
UIBuilder::label('status_msg')
    ->text('Ready')
    ->style('info')
    ->visible(true);

// Table
UIBuilder::table('users_table')
    ->title('Users')
    ->addHeader('Name')
    ->rows([['John'], ['Jane']]);
```

## Manipulación de Árbol

```php
// Agregar
$container->add(UIBuilder::button('btn1'));
$container->addMany([$btn1, $btn2]);

// Buscar
$element = $container->find(32600002);  // Por ID

// Remover
$container->remove(32600002);

// Actualizar
$container->update(32600002, $newButton);

// Limpiar
$container->clear();

// Verificar
if ($container->has(32600002)) { }
$count = $container->count();
```

## JSON Output

```json
{
    "32600001": {
        "type": "container",
        "name": "game_lobby_screen",  // ⭐ Para referencia
        "parent": "canvas",              // ⭐ Dónde renderizar
        "elements": {
            "32600002": {
                "type": "button",
                "name": "new_game"      // ⭐ Para identificación
            }
        }
    }
}
```

## Flujo Cliente-Servidor

```php
// 1. Servidor construye
$screen = UIBuilder::container('game_screen')
    ->slot('canvas');

// 2. Cliente renderiza
const slot = document.querySelector(`[data-slot="${screen.slot}"]`);

// 3. Cliente envía evento
POST /api/action { component_name: "new_game" }

// 4. Servidor procesa por name
if ($request->component_name === 'new_game') { }

// 5. Servidor envía update
return ['target_name' => 'new_game', 'enabled' => false];

// 6. Cliente actualiza
const elem = document.querySelector('[data-name="new_game"]');
```

## Casos de Uso

### Container Anidado
```php
$root = UIBuilder::container('root');
$child = UIBuilder::container('child');
$child->add(UIBuilder::button('btn1'));
$root->add($child->getContainer());
```

### Tabla con Acciones
```php
$table = UIBuilder::table('games');
foreach ($games as $game) {
    $actions = UIBuilder::container("game_{$game->id}_actions");
    $actions->add(UIBuilder::button("play_{$game->id}"));
    $table->addRow([..., $actions->build()]);
}
```

### Actualización Dinámica
```php
// Servidor
return [
    'updates' => [
        ['target_name' => 'score', 'text' => 'Score: 100']
    ]
];
```

## Buenas Prácticas

✅ **DO**
```php
UIBuilder::container('game_lobby_screen')  // Descriptivo
UIBuilder::button('new_game')              // Claro
->slot('canvas')                           // Especificar slot
```

❌ **DON'T**
```php
UIBuilder::container('container1')         // Genérico
UIBuilder::button('btn')                   // No descriptivo
```

## Documentación Completa

- `ui-builder-architecture-final.md` - Arquitectura completa
- `ui-builder-name-attribute-guide.md` - Guía del atributo name
- `ui-builder-visual-summary.md` - Resumen visual
- `FIX_DUPLICATE_IDS.md` - Solución de IDs duplicados

---

**Última Actualización:** 18 de Octubre de 2025
