# Sistema de Slots en UI Builder

## Fecha: 18 de Octubre de 2025

---

## 🎯 Propósito del Sistema de Slots

El atributo **`slot`** es un mecanismo de **vinculación padre-hijo** que permite al cliente entender:

1. **Dónde debe renderizarse un componente** (en qué contenedor padre)
2. **Cuándo debe eliminarse un componente** (slot = null)
3. **Actualizaciones dinámicas** de la estructura de UI

---

## 📊 Tipos de Valores de Slot

| Tipo | Ejemplo | Significado |
|------|---------|-------------|
| `int` | `32600001` | ID del contenedor padre donde se debe renderizar |
| `string` | `"canvas"` | Nombre del contenedor padre (para slots conocidos) |
| `null` | `null` | El componente debe ser eliminado del cliente |

---

## 🔄 Flujo Automático de Slots

### 1. **Al agregar un elemento a un contenedor**

```php
$container = UIBuilder::container('main');
$button = UIBuilder::button('submit')->label('Submit');

$container->add($button);
// ✅ Automáticamente: $button->slot = $container->getId()
```

**JSON Resultante:**
```json
{
    "1": {
        "type": "container",
        "name": "main",
        "parent": null,
        "elements": {
            "2": {
                "type": "button",
                "name": "submit",
                "parent": 1,  // ← Asignado automáticamente
                "label": "Submit"
            }
        }
    }
}
```

### 2. **Al remover un elemento de un contenedor**

```php
$container->remove($button->getId());
// ✅ Automáticamente: $button->slot = null
```

**Significado:** El cliente debe **eliminar** este componente de su DOM/UI.

### 3. **Slots manuales (para contenedores raíz)**

```php
$screen = UIBuilder::container('game_screen');
$screen->slot('canvas'); // String manual
```

**Uso:** Para contenedores raíz que se renderizan en slots predefinidos del cliente.

---

## 🏗️ Arquitectura Implementada

### Cambios en `UIElement` (Interfaz)

```php
interface UIElement
{
    /**
     * Get the slot where this element should be rendered
     * @return int|string|null
     */
    public function getParent(): int|string|null;

    /**
     * Set the slot where this element should be rendered
     * @param int|string|null $slot
     * @return self
     */
    public function setParent(int|string|null $slot): self;
}
```

### Cambios en `UIComponent` (Clase Base)

```php
abstract class UIComponent implements UIElement
{
    protected int|string|null $slot = null;
    
    public function __construct(?string $name = null)
    {
        // ...
        $this->config = array_merge([
            'type' => $this->type,
            'visible' => true,
            'slot' => null,  // ← Inicializado en null
        ], $this->getDefaultConfig());
    }
    
    public function getParent(): int|string|null
    {
        return $this->slot;
    }
    
    public function setParent(int|string|null $slot): self
    {
        $this->slot = $slot;
        $this->config['slot'] = $slot;
        return $this;
    }
    
    public function slot(int|string|null $slot): self
    {
        return $this->setParent($slot);
    }
}
```

### Cambios en `UIContainer`

```php
class UIContainer implements UIElement
{
    protected int|string|null $slot = null;
    
    public function add(UIElement $element): self
    {
        // ...
        
        // ✅ Asignar automáticamente el slot al ID del padre
        $element->setParent($this->id);
        
        $this->children[$elementId] = $element;
        return $this;
    }
    
    public function remove(string $elementId): self
    {
        // ...
        
        // ✅ Marcar para eliminación estableciendo slot = null
        $this->children[$elementId]->setParent(null);
        
        unset($this->children[$elementId]);
        return $this;
    }
}
```

---

## 📝 Casos de Uso

### Caso 1: Estructura Simple

```php
$screen = UIBuilder::container('main_screen');
$screen->slot('canvas'); // Slot manual

$header = UIBuilder::container('header');
$screen->add($header); // header->slot = ID de screen

$button = UIBuilder::button('btn')->label('Click');
$header->add($button); // button->slot = ID de header
```

**JSON:**
```json
{
    "1": {
        "type": "container",
        "name": "main_screen",
        "parent": "canvas",
        "elements": {
            "2": {
                "type": "container",
                "name": "header",
                "parent": 1,
                "elements": {
                    "3": {
                        "type": "button",
                        "name": "btn",
                        "parent": 2,
                        "label": "Click"
                    }
                }
            }
        }
    }
}
```

**Interpretación del Cliente:**
1. Renderizar container `1` en el slot `"canvas"`
2. Renderizar container `2` dentro del container `1`
3. Renderizar button `3` dentro del container `2`

---

### Caso 2: Eliminación de Elementos

```php
$container = UIBuilder::container();
$button = UIBuilder::button()->label('Delete Me');

$container->add($button);
// button->slot = container->getId()

// Luego...
$container->remove($button->getId());
// button->slot = null
```

**JSON del elemento eliminado:**
```json
{
    "3": {
        "type": "button",
        "parent": null,  // ← Cliente debe eliminar este elemento
        "label": "Delete Me"
    }
}
```

**Interpretación del Cliente:**
```javascript
if (component.slot === null) {
    // Eliminar este componente del DOM
    removeComponentFromDOM(component.id);
}
```

---

### Caso 3: Mover Elemento entre Contenedores

```php
$container1 = UIBuilder::container('left');
$container2 = UIBuilder::container('right');
$button = UIBuilder::button('movable')->label('Move Me');

// Agregar a container1
$container1->add($button);
// button->slot = container1->getId()

// Remover de container1
$container1->remove($button->getId());
// button->slot = null (marcado para eliminación)

// Agregar a container2
$container2->add($button);
// button->slot = container2->getId() (nuevo padre)
```

**Flujo en el Cliente:**

1. **Primera actualización:** `button.slot = id_container1`
   - Renderizar botón en `container1`

2. **Segunda actualización:** `button.slot = null`
   - Eliminar botón del DOM (temporalmente)

3. **Tercera actualización:** `button.slot = id_container2`
   - Renderizar botón en `container2`

---

### Caso 4: Slots Manuales (String)

```php
$sidebar = UIBuilder::container('sidebar_panel');
$sidebar->slot('sidebar'); // String conocido por el cliente

$content = UIBuilder::container('main_content');
$content->slot('content'); // String conocido por el cliente
```

**JSON:**
```json
{
    "1": {
        "type": "container",
        "name": "sidebar_panel",
        "parent": "sidebar"
    },
    "2": {
        "type": "container",
        "name": "main_content",
        "parent": "content"
    }
}
```

**Interpretación del Cliente:**
```javascript
// El cliente tiene slots predefinidos:
const slots = {
    'sidebar': document.querySelector('#sidebar-slot'),
    'content': document.querySelector('#content-slot'),
    'canvas': document.querySelector('#canvas-slot')
};

// Renderizar según el slot string
if (typeof component.slot === 'string') {
    const targetParent = slots[component.slot];
    targetParent.appendChild(componentElement);
}
```

---

## 🎯 Ejemplo Real: GameLobbyScreenService

```php
class GameLobbyScreenService
{
    public function buildUI()
    {
        $screen = UIBuilder::container('game_lobby_screen');
        $screen->slot('canvas'); // Slot raíz
        
        $newGameBtn = UIBuilder::button()
            ->label('New Game')
            ->action('create_new_game');
        
        $screen->add($newGameBtn);
        // newGameBtn->slot = screen->getId()
        
        return $screen->toJson();
    }
}
```

**JSON:**
```json
{
    "32600001": {
        "type": "container",
        "name": "game_lobby_screen",
        "parent": "canvas",
        "elements": {
            "32600002": {
                "type": "button",
                "parent": 32600001,
                "label": "New Game",
                "action": "create_new_game"
            }
        }
    }
}
```

---

## 🔍 Verificación en Tests

### Test 1: Agregar elemento

```php
$container = UIBuilder::container();
$button = UIBuilder::button();

$container->add($button);

assert($button->getParent() === $container->getId()); // ✅
```

### Test 2: Remover elemento

```php
$container->remove($button->getId());

assert($button->getParent() === null); // ✅
```

### Test 3: JSON output

```php
$json = $container->toJson();
$buttonData = $json[$container->getId()]['elements'][$button->getId()];

assert($buttonData['slot'] === $container->getId()); // ✅
```

---

## 📈 Beneficios del Sistema

### 1. **Renderizado Automático**

El cliente siempre sabe dónde renderizar cada componente sin necesidad de lógica adicional.

### 2. **Gestión de Eliminaciones**

```php
if (component.slot === null) {
    deleteComponent(component.id);
}
```

### 3. **Actualizaciones Incrementales**

Solo enviar los componentes que cambiaron:

```php
// Servidor solo envía:
{
    "32600005": {
        "type": "button",
        "parent": null  // Cliente entiende: eliminar
    }
}
```

### 4. **Trazabilidad**

Cada componente sabe a qué padre pertenece en todo momento.

### 5. **Flexibilidad**

- **int**: Referencias dinámicas (IDs)
- **string**: Slots conocidos/predefinidos
- **null**: Eliminaciones explícitas

---

## 🎓 Reglas de Negocio

| Acción | Efecto en `slot` |
|--------|------------------|
| `container->add(element)` | `element->slot = container->getId()` |
| `container->remove(id)` | `element->slot = null` |
| `element->slot(value)` | `element->slot = value` (manual) |
| Elemento sin agregar | `slot = null` |

---

## 🔄 Compatibilidad

### ✅ Backward Compatible

- Todos los tests existentes pasan
- No rompe código legacy
- Slots inicializados en `null` por defecto

### ✅ Tests Pasando

```bash
./test.ps1 bba  # ✅ 4/4 passed
./test.ps1 cnt  # ✅ 5/5 passed
```

---

## 📁 Archivos Modificados

1. ✅ `app/Services/UI/Contracts/UIElement.php`
   - Agregados métodos `getParent()` y `setParent()`

2. ✅ `app/Services/UI/Components/UIComponent.php`
   - Propiedad `$slot` agregada
   - Métodos `getParent()`, `setParent()`, `slot()` implementados
   - Inicialización en constructor

3. ✅ `app/Services/UI/Components/UIContainer.php`
   - Propiedad `$slot` agregada
   - Métodos `getParent()`, `setParent()`, `slot()` implementados
   - `add()` establece slot del hijo automáticamente
   - `remove()` establece slot a null automáticamente

4. ✅ `app/Services/UI/Components/ContainerBuilder.php`
   - Actualizado `slot()` para aceptar `int|string|null`
   - Agregados métodos de delegación: `getId()`, `remove()`, `toJson()`, etc.
   - `add()` desempaca `ContainerBuilder` automáticamente

---

## ✅ Estado Final

```
✅ Slot generalizado para todos los componentes (int|string|null)
✅ Asignación automática en add() → slot = parent ID
✅ Marcado automático en remove() → slot = null
✅ API fluida: ->slot(value)
✅ Tests pasando (BBA: 4/4, CNT: 5/5)
✅ Backward compatible
✅ Documentación completa
```

---

**Última Actualización:** 18 de Octubre de 2025
