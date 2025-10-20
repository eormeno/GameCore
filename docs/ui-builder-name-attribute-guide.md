# Guía: Atributo "name" en Componentes UI

## Fecha: 18 de Octubre de 2025

---

## 🎯 Propósito del Atributo `name`

El atributo `name` es un **identificador semántico** que sirve para **referenciar componentes entre diferentes contextos** (principalmente cliente-servidor), mientras que el `id` es un identificador técnico interno único generado automáticamente.

---

## 🔑 Diferencias: `id` vs `name`

| Aspecto | `id` | `name` |
|---------|------|--------|
| **Tipo** | `int` (numérico) | `string` (texto) |
| **Generación** | Automática (auto-incremento) | Manual (definida por desarrollador) |
| **Propósito** | Identificación técnica interna | Identificación semántica/referencia |
| **Unicidad** | Garantizada globalmente | Recomendada en su contexto |
| **Uso principal** | Operaciones de árbol (`find`, `remove`, `update`) | Comunicación cliente-servidor |
| **Requerido** | Sí (siempre existe) | No (opcional pero importante) |
| **Ejemplo** | `32600001` | `"canvas"`, `"main_menu"`, `"game_board"` |

---

## 📋 Casos de Uso del Atributo `name`

### 1. **Slots de Renderizado (Caso Principal)**

El cliente tiene una estructura predefinida con "slots" donde puede renderizar contenido dinámico.

#### Ejemplo: Cliente con estructura base
```html
<!-- Cliente tiene esta estructura HTML -->
<div id="header-slot"></div>
<div id="canvas-slot"></div>    <!-- Slot principal del juego -->
<div id="sidebar-slot"></div>
<div id="footer-slot"></div>
```

#### Servidor: Especificar dónde renderizar
```php
// En GameLobbyScreenService.php
$container = UIBuilder::container('game_lobby_screen')
    ->slot('canvas')  // 🎯 Le dice al cliente: "Renderízame en el slot 'canvas'"
    ->layout(LayoutType::VERTICAL)
    ->title('Game Lobby');
```

#### JSON enviado al cliente:
```json
{
    "32600001": {
        "type": "container",
        "name": "game_lobby_screen",  // 📌 Nombre para referencia
        "parent": "canvas",              // 🎯 Dónde renderizar
        "layout": "vertical",
        "title": "Game Lobby",
        "elements": { ... }
    }
}
```

#### Cliente interpreta:
```javascript
// Cliente recibe el JSON y sabe dónde renderizar
const container = response[32600001];
const targetParent = document.getElementById(`${container.slot}-slot`);
targetParent.innerHTML = renderComponent(container);
```

---

### 2. **Actualización Parcial de UI**

El servidor puede actualizar componentes específicos sin reconstruir toda la interfaz.

#### Ejemplo: Actualizar solo un contador
```php
// Server-side: Actualizar solo el contador de puntos
$pointsLabel = UIBuilder::label('points_counter')
    ->text("Points: {$userPoints}")
    ->style('info');

// Enviar al cliente
return [
    'action' => 'update_component',
    'target_name' => 'points_counter',  // 🎯 Cliente busca por name
    'component' => $pointsLabel->toJson()
];
```

#### Cliente recibe:
```javascript
// Cliente puede encontrar el componente por name
function updateComponent(targetName, newData) {
    const element = document.querySelector(`[data-name="${targetName}"]`);
    if (element) {
        element.update(newData);
    }
}
```

---

### 3. **Eventos del Cliente hacia el Servidor**

El cliente necesita informar al servidor qué componente generó un evento.

#### Ejemplo: Click en un botón
```php
// Server-side: Define el botón
UIBuilder::button('play_button')
    ->label('Play Game')
    ->action('start_game');
```

#### Cliente envía evento:
```javascript
// Cliente informa qué botón fue clickeado
fetch('/api/game/event', {
    method: 'POST',
    body: JSON.stringify({
        component_name: 'play_button',  // 🎯 Identifica el componente
        action: 'start_game',
        user_id: currentUser.id
    })
});
```

#### Servidor procesa:
```php
// Servidor sabe qué acción tomar basado en el name
if ($request->component_name === 'play_button') {
    return $this->startGame($user, $gameApp);
}
```

---

### 4. **Validación de Estado en el Cliente**

El cliente valida que ciertos componentes requeridos estén presentes.

#### Ejemplo: Validar estructura esperada
```javascript
// Cliente valida que la pantalla tiene los componentes esperados
function validateGameLobbyScreen(response) {
    const requiredComponents = ['new_game', 'saved_games', 'warning_message'];
    
    for (const name of requiredComponents) {
        if (!findComponentByName(response, name)) {
            throw new Error(`Missing required component: ${name}`);
        }
    }
    
    return true;
}
```

---

## 💡 Buenas Prácticas

### ✅ **DO: Usar `name` descriptivos y semánticos**

```php
// ✅ BUENO: Nombres claros y descriptivos
UIBuilder::container('game_lobby_screen')
UIBuilder::button('new_game')
UIBuilder::label('warning_message')
UIBuilder::table('saved_games')
UIBuilder::container('player_actions')
```

### ❌ **DON'T: Usar nombres técnicos o genéricos**

```php
// ❌ MALO: Nombres genéricos sin contexto
UIBuilder::container('container1')
UIBuilder::button('btn')
UIBuilder::label('lbl')

// ❌ MALO: IDs numéricos como nombre
UIBuilder::button('123')
UIBuilder::label('456')
```

---

### ✅ **DO: Usar `name` para componentes que necesitan ser referenciados**

```php
// ✅ BUENO: Componentes importantes tienen name
$screen = UIBuilder::container('game_lobby_screen')  // Slot target
    ->slot('canvas');

$screen->add(
    UIBuilder::button('new_game')  // Acción del usuario
        ->action('create_game')
);

$screen->add(
    UIBuilder::label('error_message')  // Se actualiza dinámicamente
        ->visible(false)
);
```

### ❌ **DON'T: Omitir `name` en componentes importantes**

```php
// ❌ MALO: Componentes críticos sin name
$screen = UIBuilder::container()  // ¿Dónde renderizar?
    ->slot('canvas');

$screen->add(
    UIBuilder::button()  // ¿Cómo identificar este botón?
        ->action('create_game')
);
```

---

### ✅ **DO: Usar convenciones de nombres consistentes**

```php
// ✅ BUENO: Convención snake_case consistente
UIBuilder::container('main_menu')
UIBuilder::button('start_game')
UIBuilder::label('player_score')
UIBuilder::table('leaderboard_table')

// ✅ BUENO: Prefijos para agrupación
UIBuilder::button('player_action_attack')
UIBuilder::button('player_action_defend')
UIBuilder::button('player_action_heal')
```

---

### ✅ **DO: Usar `name` para componentes dinámicos**

```php
// ✅ BUENO: IDs dinámicos basados en datos
foreach ($games as $game) {
    $container->add(
        UIBuilder::container("game_{$game->id}_actions")
            ->layout(LayoutType::HORIZONTAL)
    );
}

// Resultado:
// - game_1_actions
// - game_2_actions
// - game_3_actions
```

---

## 🔍 Ejemplo Completo: GameLobbyScreenService

```php
class GameLobbyScreenService
{
    public function getGameLobbyScreen(User $user, GameApp $gameApp): array
    {
        // 1. Container principal con name y slot
        $container = UIBuilder::container('game_lobby_screen')  // 📌 Name para referencia
            ->slot('canvas')       // 🎯 Dónde renderizar en el cliente
            ->layout(LayoutType::VERTICAL)
            ->title('Game Lobby');

        // 2. Botón con name para identificar la acción
        $container->add(
            UIBuilder::button('new_game')  // 📌 Cliente puede referenciar
                ->label('New Game')
                ->action('create_new_game')  // 🎯 Acción a ejecutar
                ->icon('plus')
        );

        // 3. Label con name para actualizaciones dinámicas
        $container->add(
            UIBuilder::label('warning_message')  // 📌 Se puede actualizar después
                ->text('Max games reached')
                ->style('warning')
                ->visible(!$canCreateNewGame)
        );

        // 4. Tabla con name para referencia
        $container->add(
            UIBuilder::table('saved_games')  // 📌 Cliente puede buscar esta tabla
                ->title('Saved Games')
                ->rows($rows)
        );

        return $container->build();
    }
}
```

### JSON Resultante:
```json
{
    "32600001": {
        "type": "container",
        "name": "game_lobby_screen",   // 📌 Para referencia cliente-servidor
        "parent": "canvas",               // 🎯 Dónde renderizar
        "layout": "vertical",
        "elements": {
            "32600002": {
                "type": "button",
                "name": "new_game",     // 📌 Identificación semántica
                "label": "New Game",
                "action": "create_new_game"
            },
            "32600003": {
                "type": "label",
                "name": "warning_message",  // 📌 Para actualizaciones
                "text": "Max games reached"
            },
            "32600007": {
                "type": "table",
                "name": "saved_games",      // 📌 Referencia clara
                "title": "Saved Games"
            }
        }
    }
}
```

---

## 🎯 Flujo Completo: Cliente ↔ Servidor

### 1. **Servidor → Cliente: Renderizado Inicial**
```php
// Servidor envía la UI
$screen = UIBuilder::container('game_lobby_screen')
    ->slot('canvas')
    ->build();

return response()->json($screen);
```

### 2. **Cliente: Renderiza en el Slot Correcto**
```javascript
// Cliente recibe y renderiza
const response = await fetch('/api/game/lobby');
const screen = response[Object.keys(response)[0]];

// Usa el slot para saber dónde renderizar
const targetParent = document.querySelector(`[data-slot="${screen.slot}"]`);
targetParent.innerHTML = renderComponent(screen);
```

### 3. **Cliente → Servidor: Evento de Usuario**
```javascript
// Usuario clickea el botón "new_game"
button.addEventListener('click', () => {
    fetch('/api/game/action', {
        method: 'POST',
        body: JSON.stringify({
            component_name: 'new_game',  // 🎯 Identifica el componente
            action: 'create_new_game'
        })
    });
});
```

### 4. **Servidor: Procesa y Actualiza**
```php
// Servidor procesa la acción
public function handleAction(Request $request)
{
    if ($request->component_name === 'new_game') {
        $game = $this->createNewGame($user);
        
        // Actualiza el label de warning
        return [
            'update' => [
                'target_name' => 'warning_message',
                'visible' => true,
                'text' => 'Game created!'
            ]
        ];
    }
}
```

### 5. **Cliente: Actualiza Componente Específico**
```javascript
// Cliente actualiza solo el componente afectado
const response = await fetch('/api/game/action', { ... });

if (response.update) {
    const component = findComponentByName(response.update.target_name);
    component.update(response.update);
}
```

---

## 📊 Resumen de Beneficios

| Beneficio | Descripción |
|-----------|-------------|
| **🎯 Targeting Preciso** | Cliente sabe exactamente dónde renderizar cada componente |
| **🔄 Actualizaciones Parciales** | Solo actualizar componentes específicos sin re-renderizar todo |
| **📝 Código Legible** | Nombres semánticos más fáciles de entender que IDs numéricos |
| **🐛 Debugging Facilitado** | Más fácil encontrar componentes en logs y debugging |
| **🔗 Comunicación Clara** | Protocolo claro entre cliente y servidor |
| **♻️ Reutilización** | Componentes pueden ser referenciados múltiples veces |

---

## 🎓 Conclusión

El atributo `name` es **esencial** para la comunicación entre cliente y servidor:

- ✅ **`id`**: Identificación técnica interna (auto-generado, único)
- ✅ **`name`**: Identificación semántica para referencia (manual, descriptivo)

**Regla de Oro:** Si un componente necesita ser referenciado desde el cliente o actualizado dinámicamente, **siempre asigna un `name` descriptivo**.

---

**Última Actualización:** 18 de Octubre de 2025
