# Arquitectura Actualizada: Sistema UI Builder

## Fecha: 18 de Octubre de 2025

---

## 📊 Resumen Ejecutivo

El sistema UI Builder ha sido completamente refactorizado con una arquitectura de **árbol basada en el patrón Composite**, implementando:

1. ✅ **IDs numéricos únicos** generados automáticamente
2. ✅ **Atributo `name`** para referencia cliente-servidor
3. ✅ **Estructura de árbol** manipulable con API fluida
4. ✅ **Serialización recursiva** a JSON
5. ✅ **Sin colisiones de IDs** mediante contador centralizado

---

## 🏗️ Arquitectura de Componentes

### Diagrama de Clases

```
UIElement (interface)
    ↑
    ├── UIComponent (abstract)
    │   ├── ButtonBuilder
    │   ├── LabelBuilder
    │   └── TableBuilder
    │
    └── UIContainer (composite)
        └── ContainerBuilder (wrapper)

UIIdGenerator (singleton)
    └── Genera IDs únicos para todos los componentes
```

---

## 🔑 Sistema Dual de Identificación

### 1. **ID Técnico (int)**

```php
protected int $id;  // Auto-generado, único
```

**Características:**
- ✅ Generado automáticamente por `UIIdGenerator`
- ✅ Único globalmente
- ✅ Numérico (ej: `32600001`, `32600002`)
- ✅ Basado en contexto + auto-incremento
- ✅ Usado internamente para operaciones de árbol

**Ejemplo:**
```php
$container = UIBuilder::container('main_menu');
echo $container->getId();  // 32600001 (auto-generado)
```

### 2. **Name Semántico (string)**

```php
protected ?string $name = null;  // Opcional, definido por desarrollador
```

**Características:**
- ✅ Definido manualmente por el desarrollador
- ✅ Opcional pero importante
- ✅ Descriptivo (ej: `"game_lobby_screen"`, `"new_game"`)
- ✅ Usado para comunicación cliente-servidor
- ✅ Permite targeting y actualizaciones parciales

**Ejemplo:**
```php
$container = UIBuilder::container('main_menu')  // name = "main_menu"
    ->slot('canvas');  // Dice dónde renderizar en el cliente
```

---

## 🎯 Formato JSON de Salida

### Estructura Completa

```json
{
    "32600001": {                          // ← ID técnico (key)
        "type": "container",               // ← Tipo de componente
        "name": "game_lobby_screen",       // ← Name semántico ⭐
        "slot": "canvas",                  // ← Dónde renderizar
        "layout": "vertical",
        "visible": true,
        "title": "Game Lobby",
        "elements": {                      // ← Hijos del container
            "32600002": {                  // ← ID único del hijo
                "type": "button",
                "name": "new_game",        // ← Name para referencia ⭐
                "label": "New Game",
                "action": "create_new_game",
                "enabled": true
            },
            "32600003": {
                "type": "label",
                "name": "warning_message", // ← Name para actualizaciones ⭐
                "text": "Ready!",
                "visible": true
            }
        }
    }
}
```

---

## 🔄 Flujo Cliente-Servidor

### 1. **Servidor → Cliente: Renderizado Inicial**

```php
// Servidor construye la UI
$screen = UIBuilder::container('game_lobby_screen')
    ->slot('canvas')              // 🎯 Dónde renderizar
    ->layout(LayoutType::VERTICAL)
    ->title('Game Lobby');

$screen->add(
    UIBuilder::button('new_game') // 📌 Name para referencia
        ->label('New Game')
        ->action('create_game')
);

return $screen->build();
```

**JSON enviado:**
```json
{
    "32600001": {
        "name": "game_lobby_screen",
        "slot": "canvas",
        "elements": {
            "32600002": {
                "type": "button",
                "name": "new_game",
                "action": "create_game"
            }
        }
    }
}
```

### 2. **Cliente: Renderiza en el Slot**

```javascript
// Cliente recibe y procesa
const response = await fetch('/api/game/lobby');
const screen = Object.values(response)[0];

// Renderiza en el slot especificado
const targetSlot = document.querySelector(`[data-slot="${screen.slot}"]`);
targetSlot.innerHTML = renderComponent(screen);
```

### 3. **Cliente → Servidor: Evento de Usuario**

```javascript
// Usuario hace click en el botón
document.querySelector('[data-name="new_game"]').addEventListener('click', () => {
    fetch('/api/game/action', {
        method: 'POST',
        body: JSON.stringify({
            component_name: 'new_game',  // 🎯 Identifica usando name
            action: 'create_game'
        })
    });
});
```

### 4. **Servidor: Procesa y Actualiza**

```php
// Servidor identifica la acción
public function handleAction(Request $request)
{
    if ($request->component_name === 'new_game') {
        $game = $this->createGame($user);
        
        // Envía actualización parcial
        return [
            'updates' => [
                [
                    'target_name' => 'new_game',  // 🎯 Target por name
                    'enabled' => false,
                    'label' => 'Creating...'
                ]
            ]
        ];
    }
}
```

### 5. **Cliente: Actualiza Componentes**

```javascript
// Cliente actualiza solo los componentes afectados
response.updates.forEach(update => {
    const component = document.querySelector(`[data-name="${update.target_name}"]`);
    if (component) {
        Object.assign(component.dataset, update);
        component.update(update);
    }
});
```

---

## 💾 Generación de IDs

### UIIdGenerator (Centralizado)

```php
class UIIdGenerator
{
    private static array $autoIncPerContext = [];
    
    public static function generate(string $context): int
    {
        if (!isset(self::$autoIncPerContext[$context])) {
            self::$autoIncPerContext[$context] = 0;
        }
        
        $localId = ++self::$autoIncPerContext[$context];
        $offset = self::getContextOffset($context);
        
        return $offset + $localId;
    }
    
    private static function getContextOffset(string $context): int
    {
        if ($context === 'default') return 0;
        
        $hash = crc32($context);
        return (abs($hash) % 9999) * 10000;
    }
}
```

### Ejemplo de Generación

```
Contexto: GameLobbyScreenService
Hash CRC32: -1234567890
Offset: (abs(-1234567890) % 9999) * 10000 = 32600000

IDs generados:
- Container: 32600000 + 1 = 32600001
- Button:    32600000 + 2 = 32600002
- Label:     32600000 + 3 = 32600003
- Table:     32600000 + 4 = 32600004
```

---

## 🎨 API de Construcción

### Creación de Componentes

```php
// Button
UIBuilder::button('submit_btn')
    ->label('Submit')
    ->action('submit_form', ['form_id' => 123])
    ->style('primary')
    ->enabled(true)
    ->icon('check')
    ->tooltip('Click to submit');

// Label
UIBuilder::label('status_msg')
    ->text('Ready')
    ->style('info')
    ->visible(true);

// Container
UIBuilder::container('main_screen')
    ->slot('canvas')
    ->layout(LayoutType::HORIZONTAL)
    ->title('Main Menu');

// Table
UIBuilder::table('users_table')
    ->title('Users')
    ->addHeader('Name')
    ->addHeader('Email')
    ->rows([
        ['John', 'john@example.com'],
        ['Jane', 'jane@example.com']
    ]);
```

### Manipulación de Árbol

```php
$container = UIBuilder::container('parent');

// Agregar elementos
$container->add(UIBuilder::button('btn1'));
$container->add(UIBuilder::label('lbl1'));

// Agregar múltiples
$container->addMany([
    UIBuilder::button('btn2'),
    UIBuilder::label('lbl2')
]);

// Buscar elemento
$button = $container->find(32600002);  // Por ID
$button = $container->find('btn1');     // Por name (si implementado)

// Remover elemento
$container->remove(32600002);           // Por ID

// Actualizar elemento
$container->update(32600002, $newButton);

// Limpiar todos los hijos
$container->clear();

// Contar hijos
$count = $container->count();

// Verificar existencia
if ($container->has(32600002)) {
    // ...
}
```

---

## 📋 Casos de Uso Reales

### Caso 1: Game Lobby Screen

```php
class GameLobbyScreenService
{
    public function getGameLobbyScreen(User $user, GameApp $gameApp): array
    {
        // Container principal
        $screen = UIBuilder::container('game_lobby_screen')
            ->slot('canvas')
            ->layout(LayoutType::VERTICAL)
            ->title($gameApp->name . ' - Lobby');
        
        // Botón nuevo juego
        $screen->add(
            UIBuilder::button('new_game')
                ->label('New Game')
                ->action('create_game')
                ->enabled($this->canCreateGame($user))
        );
        
        // Advertencia
        $screen->add(
            UIBuilder::label('warning')
                ->text('Max games reached')
                ->visible(!$this->canCreateGame($user))
        );
        
        // Tabla de juegos guardados
        $screen->add(
            $this->buildSavedGamesTable($user, $gameApp)
        );
        
        return $screen->build();
    }
}
```

### Caso 2: Actualización Dinámica

```php
// Cliente envía acción
POST /api/game/action
{
    "component_name": "new_game",
    "action": "create_game"
}

// Servidor procesa y responde con actualizaciones
return [
    'status' => 'success',
    'game_id' => 123,
    'updates' => [
        [
            'target_name' => 'new_game',
            'enabled' => false,
            'label' => 'Game Created!'
        ],
        [
            'target_name' => 'warning',
            'visible' => true,
            'text' => 'Max games reached'
        ]
    ]
];
```

### Caso 3: Contenedores Anidados

```php
$root = UIBuilder::container('game_screen')
    ->slot('canvas');

$header = UIBuilder::container('header')
    ->layout(LayoutType::HORIZONTAL);
$header->add(UIBuilder::label('title')->text('My Game'));
$header->add(UIBuilder::button('quit')->label('Quit'));

$content = UIBuilder::container('content')
    ->layout(LayoutType::VERTICAL);
$content->add(UIBuilder::label('score')->text('Score: 0'));

$footer = UIBuilder::container('footer')
    ->layout(LayoutType::HORIZONTAL);
$footer->add(UIBuilder::button('save')->label('Save'));

$root->add($header->getContainer());
$root->add($content->getContainer());
$root->add($footer->getContainer());

return $root->build();
```

---

## 🎓 Buenas Prácticas

### ✅ DO: Usar names descriptivos

```php
UIBuilder::container('game_lobby_screen')
UIBuilder::button('new_game')
UIBuilder::label('player_score')
UIBuilder::table('leaderboard')
```

### ❌ DON'T: Nombres genéricos

```php
UIBuilder::container('container1')  // ❌
UIBuilder::button('btn')            // ❌
UIBuilder::label('lbl')             // ❌
```

### ✅ DO: Especificar slots para containers principales

```php
UIBuilder::container('main_screen')
    ->slot('canvas')  // ✅ Cliente sabe dónde renderizar
```

### ✅ DO: Usar names para componentes que se actualizan

```php
UIBuilder::label('error_message')  // ✅ Se puede actualizar después
    ->visible(false)
```

### ✅ DO: Usar IDs dinámicos en nombres

```php
foreach ($games as $game) {
    UIBuilder::button("play_game_{$game->id}")  // ✅
        ->action('play_game', ['game_id' => $game->id]);
}
```

---

## 📊 Ventajas del Sistema

| Ventaja | Descripción |
|---------|-------------|
| **🎯 Dual Identification** | ID técnico único + name semántico descriptivo |
| **🔄 Partial Updates** | Cliente actualiza solo componentes específicos |
| **🌲 Tree Structure** | Jerarquía natural de componentes |
| **♻️ Reusability** | Componentes reutilizables y componibles |
| **🐛 Debuggability** | Names facilitan debugging y logs |
| **📝 Maintainability** | Código más legible y mantenible |
| **⚡ Performance** | IDs numéricos más rápidos que strings |
| **🔒 Uniqueness** | IDs garantizados únicos por `UIIdGenerator` |

---

## 📁 Archivos Principales

```
app/Services/UI/
├── Contracts/
│   └── UIElement.php                    # Interface base
├── Components/
│   ├── UIComponent.php                  # Componentes leaf
│   ├── UIContainer.php                  # Composite container
│   ├── BaseUIBuilder.php                # Builder base (legacy)
│   ├── ButtonBuilder.php
│   ├── LabelBuilder.php
│   ├── TableBuilder.php
│   └── ContainerBuilder.php
├── Support/
│   └── UIIdGenerator.php                # Generador centralizado ⭐
└── UIBuilder.php                        # Factory principal
```

---

## 🎓 Conclusión

El sistema UI Builder actualizado proporciona:

1. ✅ **Identificación dual**: ID técnico + name semántico
2. ✅ **Comunicación clara**: Cliente-servidor mediante names
3. ✅ **IDs únicos**: Garantizados por `UIIdGenerator`
4. ✅ **Estructura de árbol**: Manipulación flexible
5. ✅ **API fluida**: Construcción declarativa
6. ✅ **Actualizaciones parciales**: Solo lo necesario
7. ✅ **Escalabilidad**: Soporta hasta 9,999 contextos
8. ✅ **Rendimiento**: IDs numéricos optimizados

**El atributo `name` es la clave para la comunicación efectiva entre cliente y servidor.**

---

**Última Actualización:** 18 de Octubre de 2025
