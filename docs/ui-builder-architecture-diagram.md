# Diagrama de Arquitectura UI Builder - Tree Structure

## Jerarquía de Clases

```
┌─────────────────────────────────────────────────────────────────┐
│                        <<interface>>                            │
│                         UIElement                               │
├─────────────────────────────────────────────────────────────────┤
│ + getId(): string                                               │
│ + getType(): string                                             │
│ + toJson(): array                                               │
│ + isVisible(): bool                                             │
│ + setVisible(bool): self                                        │
└──────────────────┬──────────────────────────────────────────────┘
                   │
         ┌─────────┴──────────┐
         │                    │
         ▼                    ▼
┌─────────────────┐  ┌──────────────────┐
│  UIComponent    │  │   UIContainer    │
│   (abstract)    │  │   (composite)    │
├─────────────────┤  ├──────────────────┤
│ # id: string    │  │ # children: []   │
│ # type: string  │  │                  │
│ # config: array │  │ + add()          │
│                 │  │ + remove()       │
│ + toJson()      │  │ + update()       │
│                 │  │ + find()         │
└────────┬────────┘  │ + getChildren()  │
         │           │ + clear()        │
         │           │ + toJson()       │
         │           └──────────────────┘
         │
    ┌────┴─────┬─────────┬──────────┐
    ▼          ▼         ▼          ▼
┌─────────┐ ┌────────┐ ┌────────┐ ┌────────┐
│ Button  │ │ Label  │ │ Table  │ │  ...   │
│ Builder │ │ Builder│ │ Builder│ │        │
└─────────┘ └────────┘ └────────┘ └────────┘
```

## Estructura de Árbol UI (Ejemplo)

```
game_lobby_screen:container (ROOT)
│
├── slot: "canvas"
├── layout: "vertical"
├── title: "Game Lobby"
│
└── children:
    │
    ├─► new_game:button
    │   ├── label: "New Game"
    │   ├── action: "create_new_game"
    │   ├── style: "primary"
    │   └── icon: "plus"
    │
    ├─► warning_message:label
    │   ├── text: "Limit reached"
    │   ├── style: "warning"
    │   └── visible: false
    │
    └─► saved_games:table
        ├── title: "Saved Games"
        ├── headers: [...]
        └── rows:
            └── [
                 1,
                 "Game 1",
                 "Ready",
                 actions:container ───┐
               ]                      │
                                      │
                   ┌──────────────────┘
                   │
                   ├─► play_1:button
                   │   ├── label: "Play"
                   │   ├── icon: "play"
                   │   └── style: "success"
                   │
                   └─► delete_1:button
                       ├── label: "Delete"
                       ├── icon: "trash"
                       └── style: "danger"
```

## Flujo de Serialización (toJson)

```
┌──────────────────────────────────────────────────────┐
│  container.toJson()                                  │
│                                                      │
│  1. Crear config base del contenedor                │
│     {                                                │
│       "id:container": {                              │
│         "visible": true,                             │
│         "layout": "vertical",                        │
│         ...                                          │
│       }                                              │
│     }                                                │
│                                                      │
│  2. Iterar children y llamar toJson() recursivamente│
│     foreach ($children as $child) {                 │
│       $childJson = $child->toJson(); ◄─────────┐    │
│       merge into elements[]                    │    │
│     }                                           │    │
│                                                 │    │
│  3. Retornar JSON completo                     │    │
└─────────────────────────────────────────────────┼────┘
                                                  │
                         ┌────────────────────────┘
                         │
          ┌──────────────┴──────────────┐
          │                             │
          ▼                             ▼
    ┌────────────┐              ┌─────────────┐
    │  Button    │              │ Container   │
    │  toJson()  │              │ toJson()    │
    │            │              │             │
    │  return {  │              │ (RECURSIVO) │
    │    config  │              │             │
    │  }         │              │ Llama       │
    └────────────┘              │ toJson() en │
                                │ sus hijos   │
                                └─────────────┘
```

## Operaciones de Manipulación

### ADD Operation
```
ANTES:                    DESPUÉS:
                          
container                 container
└── (vacío)              ├── button1
                         └── label1

Código:
container.add(button1)
container.add(label1)
```

### REMOVE Operation
```
ANTES:                    DESPUÉS:

container                 container
├── button1              ├── button1
├── button2              └── button3
└── button3              

Código:
container.remove('button2:button')
```

### UPDATE Operation
```
ANTES:                    DESPUÉS:

container                 container
└── button1              └── button1 (actualizado)
    label: "Old"             label: "New"
                             enabled: false

Código:
newButton = Button('button1').label('New').enabled(false)
container.update('button1:button', newButton)
```

### FIND Operation (Recursive)
```
root:container
├── header:container
│   └── logo:label ◄────────────┐
├── content:container           │
│   ├── menu:container          │
│   │   └── item1:button        │
│   └── sidebar:container       │
└── footer:container            │
                                │
root.find('logo:label') ────────┘
└─► Busca recursivamente en toda la jerarquía
```

## Comparación: Antes vs Ahora

### ANTES (Array Concatenation)
```
┌─────────────────────────────────┐
│ buildUIElements(): array        │
│                                 │
│ $elements = [];                 │
│ $elements += button->build();   │
│ $elements += label->build();    │
│ $elements += table->build();    │
│                                 │
│ return $elements; (flat array)  │
└─────────────────────────────────┘

Problemas:
✗ No se puede modificar después
✗ Estructura plana
✗ Difícil de navegar
✗ No hay jerarquía clara
```

### AHORA (Tree Structure)
```
┌──────────────────────────────────┐
│ buildUIElements(): void          │
│                                  │
│ container->add(button);          │
│ container->add(label);           │
│ container->add(table);           │
│                                  │
│ // Manipulación dinámica         │
│ container->remove('label');      │
│ container->update('button', ...);│
│ element = container->find(...);  │
└──────────────────────────────────┘

Ventajas:
✓ Modificable en cualquier momento
✓ Estructura de árbol jerárquica
✓ Búsqueda eficiente
✓ Navegación clara
✓ Serialización automática
```

## Patrón Composite en Acción

```
                Component (UIElement)
                       │
         ┌─────────────┴─────────────┐
         │                           │
    Leaf (UIComponent)       Composite (UIContainer)
         │                           │
    ┌────┴────┐               ┌──────┴──────┐
    │         │               │              │
  Button    Label          children[]      methods
                          (UIElements)    (add, remove, ...)
                               │
                        ┌──────┴──────┐
                        │             │
                    Component     Component
                    (Button)    (Container)
                                     │
                              children[] (más elementos)
```

## Ejemplo Completo de Uso

```php
// 1. Crear contenedor raíz
$root = UIBuilder::container('app')->getContainer();
$root->slot('canvas')->layout(LayoutType::VERTICAL);

// 2. Agregar header con logo
$header = new UIContainer('header');
$header->layout(LayoutType::HORIZONTAL);
$header->add(UIBuilder::label('logo')->text('🎮 GameCore'));
$header->add(UIBuilder::label('user')->text('Welcome!'));

// 3. Agregar contenido principal
$content = new UIContainer('content');
$content->add(UIBuilder::button('play')->label('Play')->style('success'));
$content->add(UIBuilder::button('settings')->label('Settings'));

// 4. Agregar footer
$footer = new UIContainer('footer');
$footer->add(UIBuilder::label('copyright')->text('© 2025'));

// 5. Ensamblar el árbol
$root->add($header);
$root->add($content);
$root->add($footer);

// 6. Manipulación dinámica
$playButton = $root->find('play:button');
if ($playButton) {
    $root->update('play:button', 
        UIBuilder::button('play')->label('Play Now!')->enabled(true)
    );
}

// 7. Serializar a JSON
$json = $root->toJson();

// Resultado: Árbol completo serializado recursivamente
```

## Beneficios Visualizados

```
┌────────────────────────────────────────────┐
│         ANTES                              │
├────────────────────────────────────────────┤
│ • Arrays planos                            │
│ • Concatenación con +=                     │
│ • No modificable                           │
│ • Sin estructura clara                     │
│ • Difícil de testear                       │
└────────────────────────────────────────────┘
                    │
                    │ REFACTORIZACIÓN
                    ▼
┌────────────────────────────────────────────┐
│         AHORA                              │
├────────────────────────────────────────────┤
│ ✓ Árbol jerárquico                        │
│ ✓ Métodos de manipulación                 │
│ ✓ Modificable dinámicamente                │
│ ✓ Estructura clara y navegable            │
│ ✓ Fácil de testear                        │
│ ✓ Serialización automática                │
│ ✓ Búsqueda recursiva                      │
│ ✓ Type-safe con interfaces                │
└────────────────────────────────────────────┘
```

---

**Leyenda:**
- `►` = Hijo directo
- `│` = Conexión vertical
- `├──` = Rama del árbol
- `└──` = Última rama
- `◄──` = Referencia/Búsqueda
- `▼` = Flujo hacia abajo
