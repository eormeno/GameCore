# 📊 Sistema de Slots - Diagramas Visuales

---

## 🌳 Estructura de Árbol con Slots

```
┌─────────────────────────────────────────────────────┐
│ Container: "game_screen" (ID: 1)                    │
│ slot: "canvas"                                      │
│                                                     │
│  ┌──────────────────────────────────────────────┐  │
│  │ Container: "header" (ID: 2)                  │  │
│  │ slot: 1 (padre: game_screen)                 │  │
│  │                                               │  │
│  │  ┌────────────────────────────────────────┐  │  │
│  │  │ Button: "btn1" (ID: 3)                 │  │  │
│  │  │ slot: 2 (padre: header)                │  │  │
│  │  │ label: "Button 1"                      │  │  │
│  │  └────────────────────────────────────────┘  │  │
│  │                                               │  │
│  │  ┌────────────────────────────────────────┐  │  │
│  │  │ Button: "btn2" (ID: 4)                 │  │  │
│  │  │ slot: 2 (padre: header)                │  │  │
│  │  │ label: "Button 2"                      │  │  │
│  │  └────────────────────────────────────────┘  │  │
│  └──────────────────────────────────────────────┘  │
│                                                     │
│  ┌──────────────────────────────────────────────┐  │
│  │ Label: "warning" (ID: 5)                     │  │
│  │ slot: 1 (padre: game_screen)                 │  │
│  │ text: "Warning message"                      │  │
│  └──────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

**Interpretación:**
- `game_screen` (1) → Renderiza en slot `"canvas"`
- `header` (2) → Renderiza dentro de `game_screen` (1)
- `btn1` (3) → Renderiza dentro de `header` (2)
- `btn2` (4) → Renderiza dentro de `header` (2)
- `warning` (5) → Renderiza dentro de `game_screen` (1)

---

## 🔄 Flujo de Agregar Elemento

```
┌─────────────┐
│   Servidor  │
└──────┬──────┘
       │
       │ $container->add($button)
       ▼
┌─────────────────────────────────────┐
│  UIContainer::add()                 │
│                                     │
│  1. Verificar ID no duplicado       │
│  2. Asignar slot:                   │
│     $element->setSlot($this->id)    │  ◄── AUTOMÁTICO
│  3. Agregar a children[]            │
└──────┬──────────────────────────────┘
       │
       │ toJson()
       ▼
┌─────────────────────────────────────┐
│  JSON Output                        │
│                                     │
│  "2": {                             │
│    "type": "button",                │
│    "slot": 1,  ◄── ID del padre     │
│    "label": "Click"                 │
│  }                                  │
└──────┬──────────────────────────────┘
       │
       │ HTTP Response
       ▼
┌─────────────┐
│   Cliente   │
│             │
│  Interpreta:                        │
│  - slot = 1                         │
│  - Buscar container con ID 1        │
│  - Renderizar botón dentro          │
└─────────────┘
```

---

## 🗑️ Flujo de Eliminar Elemento

```
┌─────────────┐
│   Servidor  │
└──────┬──────┘
       │
       │ $container->remove($buttonId)
       ▼
┌─────────────────────────────────────┐
│  UIContainer::remove()              │
│                                     │
│  1. Verificar elemento existe       │
│  2. Marcar para eliminación:        │
│     $element->setSlot(null)         │  ◄── AUTOMÁTICO
│  3. Remover de children[]           │
└──────┬──────────────────────────────┘
       │
       │ toJson() del elemento eliminado
       ▼
┌─────────────────────────────────────┐
│  JSON Output (actualización)        │
│                                     │
│  "2": {                             │
│    "type": "button",                │
│    "slot": null,  ◄── Eliminado     │
│    "label": "Click"                 │
│  }                                  │
└──────┬──────────────────────────────┘
       │
       │ HTTP Response (incremental)
       ▼
┌─────────────┐
│   Cliente   │
│             │
│  Interpreta:                        │
│  - slot = null                      │
│  - Buscar componente con ID 2       │
│  - Eliminarlo del DOM               │
└─────────────┘
```

---

## 🔀 Flujo de Mover Elemento

```
Estado Inicial:
┌────────────────┐     ┌────────────────┐
│  Container A   │     │  Container B   │
│  (ID: 10)      │     │  (ID: 20)      │
│                │     │                │
│  ┌──────────┐  │     │                │
│  │ Button X │  │     │                │
│  │ slot: 10 │  │     │                │
│  └──────────┘  │     │                │
└────────────────┘     └────────────────┘

Paso 1: Remover de Container A
$containerA->remove($buttonX->getId())

┌────────────────┐     ┌────────────────┐
│  Container A   │     │  Container B   │
│  (ID: 10)      │     │  (ID: 20)      │
│                │     │                │
│  (vacío)       │     │                │
└────────────────┘     └────────────────┘

  ┌──────────┐
  │ Button X │
  │ slot: null │ ◄── Marcado para eliminar
  └──────────┘

Paso 2: Agregar a Container B
$containerB->add($buttonX)

┌────────────────┐     ┌────────────────┐
│  Container A   │     │  Container B   │
│  (ID: 10)      │     │  (ID: 20)      │
│                │     │                │
│  (vacío)       │     │  ┌──────────┐  │
└────────────────┘     │  │ Button X │  │
                       │  │ slot: 20 │  │ ◄── Nuevo padre
                       │  └──────────┘  │
                       └────────────────┘

JSON Updates (incremental):
┌────────────────────────────────────┐
│ Update 1: {                        │
│   "99": {                          │
│     "slot": null  ← Eliminar       │
│   }                                │
│ }                                  │
│                                    │
│ Update 2: {                        │
│   "99": {                          │
│     "slot": 20  ← Renderizar en B  │
│   }                                │
│ }                                  │
└────────────────────────────────────┘
```

---

## 🎯 Matriz de Tipos de Slot

```
┌──────────────┬──────────────┬───────────────────────┬──────────────────┐
│ Tipo         │ Ejemplo      │ Uso                   │ Cuándo           │
├──────────────┼──────────────┼───────────────────────┼──────────────────┤
│ null         │ null         │ Elemento sin padre    │ Creación inicial │
│              │              │ o marcado para        │ o eliminación    │
│              │              │ eliminar              │                  │
├──────────────┼──────────────┼───────────────────────┼──────────────────┤
│ int          │ 32600001     │ ID del contenedor     │ Relación         │
│              │              │ padre (dinámico)      │ padre-hijo       │
├──────────────┼──────────────┼───────────────────────┼──────────────────┤
│ string       │ "canvas"     │ Slot predefinido      │ Contenedores     │
│              │ "sidebar"    │ conocido por cliente  │ raíz             │
└──────────────┴──────────────┴───────────────────────┴──────────────────┘
```

---

## 📊 Comparación: Antes vs Ahora

### ❌ ANTES (sin slots)

```
Servidor:                       Cliente:
┌──────────────┐               ┌──────────────┐
│  Container   │               │   ¿Dónde     │
│    Button    │  ─────────►   │   renderizar │
│    Label     │               │   cada uno?  │
└──────────────┘               └──────────────┘
                                      │
                                      ▼
                                Lógica manual
                                compleja
```

### ✅ AHORA (con slots)

```
Servidor:                       Cliente:
┌──────────────┐               ┌──────────────┐
│  Container   │               │   Renderizar │
│  slot: 1     │  ─────────►   │   según slot │
│              │               │   (automático)│
│   Button     │               │              │
│   slot: 1    │               │   ✓ Preciso  │
│              │               │   ✓ Simple   │
│   Label      │               │   ✓ Rápido   │
│   slot: 1    │               │              │
└──────────────┘               └──────────────┘
```

---

## 🔍 Ejemplo Real: GameLobbyScreenService

### Código PHP

```php
$screen = UIBuilder::container('game_lobby_screen');
$screen->slot('canvas');  // ← Manual

$newGameBtn = UIBuilder::button()
    ->label('New Game')
    ->action('create_new_game');

$screen->add($newGameBtn);  // ← button.slot = screen.id (auto)

$warningLabel = UIBuilder::label()
    ->text('Warning: Limit reached')
    ->visible(false);

$screen->add($warningLabel);  // ← label.slot = screen.id (auto)
```

### Árbol Resultante

```
canvas (slot predefinido)
  │
  └─ Container: game_lobby_screen (ID: 32600001)
      │
      ├─ Button: New Game (ID: 32600002)
      │  └─ slot: 32600001
      │
      └─ Label: Warning (ID: 32600003)
         └─ slot: 32600001
```

### JSON Output

```json
{
    "32600001": {
        "type": "container",
        "name": "game_lobby_screen",
        "slot": "canvas",
        "elements": {
            "32600002": {
                "type": "button",
                "slot": 32600001,
                "label": "New Game",
                "action": "create_new_game"
            },
            "32600003": {
                "type": "label",
                "slot": 32600001,
                "text": "Warning: Limit reached",
                "visible": false
            }
        }
    }
}
```

### Cliente JavaScript

```javascript
// Renderizado automático basado en slots
const components = parseJSON(response);

Object.values(components).forEach(component => {
    if (component.slot === null) {
        // Eliminar
        deleteComponent(component.id);
    } 
    else if (component.slot === 'canvas') {
        // Renderizar en canvas predefinido
        canvasElement.appendChild(createComponent(component));
    }
    else if (typeof component.slot === 'number') {
        // Renderizar dentro del padre
        const parent = findById(component.slot);
        parent.appendChild(createComponent(component));
    }
});
```

---

## 🎯 Estados del Ciclo de Vida

```
Creación
   │
   ▼
┌───────────┐
│ slot: null│  ◄── Estado inicial
└─────┬─────┘
      │
      │ add() → Agregar a contenedor
      ▼
┌───────────┐
│ slot: 123 │  ◄── Asignado a padre (ID: 123)
└─────┬─────┘
      │
      │ (elemento renderizado en cliente)
      │
      │ remove() → Remover de contenedor
      ▼
┌───────────┐
│ slot: null│  ◄── Marcado para eliminar
└───────────┘
      │
      │ (cliente elimina del DOM)
      ▼
   Eliminado
```

---

## 📈 Performance: Actualizaciones Incrementales

### Escenario: Eliminar un botón de una lista de 100 elementos

#### ❌ SIN SLOTS (enviar todo)
```
Servidor envía:
- Container completo (1)
- 99 botones restantes
Total: ~100 componentes

Cliente procesa:
- Destruye DOM entero
- Recrea 99 botones
- Re-aplica estilos
- Re-bind eventos
Tiempo: ~500ms
```

#### ✅ CON SLOTS (enviar solo cambio)
```
Servidor envía:
- 1 botón con slot: null

Cliente procesa:
- Busca botón por ID
- Elimina del DOM
Tiempo: ~10ms

Reducción: 98%
```

---

## 🎨 Casos de Uso Visuales

### Caso 1: Diálogo Modal

```
┌────────────────────────────────────┐
│ Overlay (slot: "modal-layer")     │
│                                    │
│  ┌──────────────────────────────┐ │
│  │ Dialog (slot: overlay.id)    │ │
│  │                              │ │
│  │  ┌────────────────────────┐  │ │
│  │  │ Title (slot: dialog.id)│  │ │
│  │  └────────────────────────┘  │ │
│  │                              │ │
│  │  ┌────────────────────────┐  │ │
│  │  │ Content (slot: dialog)│  │ │
│  │  └────────────────────────┘  │ │
│  │                              │ │
│  │  ┌────────────────────────┐  │ │
│  │  │ [OK] [Cancel]          │  │ │
│  │  │ (slot: dialog.id)      │  │ │
│  │  └────────────────────────┘  │ │
│  └──────────────────────────────┘ │
└────────────────────────────────────┘

Cerrar modal:
  overlay.slot = null
  ↓
  Cliente elimina todo el árbol
```

### Caso 2: Lista Dinámica

```
Agregar item:
┌────────────┐
│ List       │
│ (ID: 100)  │
│            │
│ Item 1 ───────► slot: 100
│ Item 2 ───────► slot: 100
│ Item 3 ───────► slot: 100
│ [+] Nuevo      ◄── Crea Item 4, slot: 100
└────────────┘

Eliminar item 2:
  item2.slot = null
  ↓
  Cliente elimina solo Item 2
  Items 1 y 3 permanecen
```

---

## ✅ Resumen Visual

```
┌─────────────────────────────────────────────┐
│           SISTEMA DE SLOTS                  │
├─────────────────────────────────────────────┤
│                                             │
│  Tipos:                                     │
│  ┌──────┬─────────┬────────────────────┐   │
│  │ null │ int     │ string             │   │
│  │ ↓    │ ↓       │ ↓                  │   │
│  │ DEL  │ PARENT  │ PREDEFINED         │   │
│  └──────┴─────────┴────────────────────┘   │
│                                             │
│  Flujo:                                     │
│  crear → null                               │
│  add() → parent.id                          │
│  remove() → null                            │
│                                             │
│  Beneficios:                                │
│  ✓ Automático                               │
│  ✓ Preciso                                  │
│  ✓ Incremental                              │
│  ✓ Performante                              │
│                                             │
└─────────────────────────────────────────────┘
```

---

**Creado:** 18 de Octubre de 2025  
**Versión:** 1.0
