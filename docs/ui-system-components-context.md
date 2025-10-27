# UI System Components - Context Document

Este documento describe los componentes UI desarrollados en el proyecto GameCore. Usar este contexto para continuar el desarrollo con pleno conocimiento de la arquitectura implementada.

---

## 📋 SISTEMA DE TABLAS (Table Component)

### Componentes Backend

**TableBuilder** (`app/Services/UI/Components/TableBuilder.php`)
- Constructor fluido para crear tablas con rows y columns
- Métodos principales:
  - `pagination(int $itemsPerPage, int $currentPage, int $totalItems)` - Configura paginación
  - `currentPage()` - Obtiene página actual
  - `totalItems()` - Obtiene total de items
  - `header()` - Crea fila de encabezados
  - `row()` - Crea fila de datos
  - `build()` - Genera array de configuración con IDs únicos

**TableRowBuilder** (`app/Services/UI/Components/TableRowBuilder.php`)
- Construye filas de tabla
- `cell(mixed $content)` - Agrega celda con contenido (texto, botón, componente)
- Soporte para componentes complejos en celdas

**TableHeaderRowBuilder** - Construye filas de encabezado
**TableCellBuilder** - Construye celdas individuales
**TableHeaderCellBuilder** - Construye celdas de encabezado

### Componentes Frontend

**TableComponent** (`public/js/ui-renderer.js`)
```javascript
class TableComponent extends UIComponent {
    render() {
        const tableWrapper = document.createElement('div');
        tableWrapper.className = 'table-wrapper';
        
        const table = document.createElement('table');
        table.className = 'ui-table';
        
        // Renderiza headers y rows
        // Maneja paginación
    }
}
```

**Paginación:**
- Botones Previous/Next
- Números de página clickeables
- Evento `change_page` con parámetro `page`

### Estilos CSS (`public/css/ui-components.css`)
```css
.table-wrapper {
    overflow-x: auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.ui-table {
    width: 100%;
    border-collapse: collapse;
}

.pagination {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 16px;
}
```

### Ejemplo de Uso

**Backend (TableDemoService.php):**
```php
protected function buildBaseUI(...$params): UIContainer
{
    $container = UIBuilder::container('table_demo');
    
    // Paginación
    $currentPage = $params['current_page'] ?? 1;
    $itemsPerPage = 5;
    $totalItems = count($this->users);
    
    // Crear tabla
    $table = UIBuilder::table('users_table')
        ->pagination($itemsPerPage, $currentPage, $totalItems);
    
    // Header
    $table->header()
        ->cell('ID')
        ->cell('Name')
        ->cell('Email')
        ->cell('Actions');
    
    // Rows con slice para paginación
    $start = ($currentPage - 1) * $itemsPerPage;
    $pageUsers = array_slice($this->users, $start, $itemsPerPage);
    
    foreach ($pageUsers as $user) {
        $table->row()
            ->cell($user['id'])
            ->cell($user['name'])
            ->cell($user['email'])
            ->cell(
                UIBuilder::button('edit_' . $user['id'])
                    ->label('Edit #' . $user['id'])
                    ->action('edit_user', ['user_id' => $user['id']])
            );
    }
    
    $container->add($table);
    return $container;
}

// Handler de paginación
public function onChangePage(array $params): array
{
    $page = $params['page'] ?? 1;
    return $this->getUI(current_page: $page);
}
```

**Frontend:**
- El componente se renderiza automáticamente
- Click en página dispara evento `change_page`
- Actualización de UI mediante `handleUIUpdate()`

---

## 🪟 SISTEMA DE MODALES (Modal System)

### Arquitectura

**Concepto:** Los modales son servicios normales que se renderizan en un div especial con overlay. No requieren herencia especial ni métodos específicos.

### Componentes Backend

**ConfirmDialogService** (`app/Services/UI/Modals/ConfirmDialogService.php`)
- Servicio helper para diálogos de confirmación
- **NO hereda de AbstractUIService** (es un utility service)
- Método principal:

```php
public function getUI(...$params): array
{
    $title = $params['title'] ?? 'Confirmar';
    $message = $params['message'] ?? '¿Está seguro?';
    $icon = $params['icon'] ?? null; // 'question', 'info', 'warning', 'error', 'success'
    $confirmAction = $params['confirmAction'] ?? 'confirm';
    $confirmParams = $params['confirmParams'] ?? [];
    $confirmLabel = $params['confirmLabel'] ?? 'Confirmar';
    $cancelAction = $params['cancelAction'] ?? 'close_modal';
    $cancelLabel = $params['cancelLabel'] ?? 'Cancelar';
    $callerServiceId = $params['callerServiceId'] ?? null;
    
    $container = UIBuilder::container('confirm_dialog')
        ->parent('modal')  // ← KEY: renderiza en modal overlay
        ->shadow(0)
        ->rounded(4)
        ->gap(8)
        ->centerContent();
    
    // Icon con emoji grande (48px)
    if ($icon) {
        $iconEmoji = $this->getIconEmoji($icon);
        $container->add(
            UIBuilder::label('icon')
                ->text($iconEmoji)
                ->fontSize(48)
        );
    }
    
    // Botones con _caller_service_id para callback
    $confirmButton->action($confirmAction, array_merge($confirmParams, [
        '_caller_service_id' => $callerServiceId
    ]));
    
    return $container->build();
}

private function getIconEmoji(string $icon): string
{
    return match($icon) {
        'question' => '❓',
        'info' => 'ℹ️',
        'warning' => '⚠️',
        'error' => '❌',
        'success' => '✅',
        default => '❓'
    };
}
```

### Sistema de Callbacks

**AbstractUIService** - Método agregado:
```php
protected function getServiceComponentId(): int
{
    $ui = $this->getStoredUI();
    foreach ($ui as $id => $component) {
        if ($component['type'] === 'container') {
            return (int)$id;
        }
    }
    // Fallback
    return UIIdGenerator::generateFromName(static::class, 'service_root');
}
```

**UIEventController** - Routing con caller service:
```php
public function handleEvent(Request $request): JsonResponse
{
    // ...
    $callerServiceId = $parameters['_caller_service_id'] ?? null;
    unset($parameters['_caller_service_id']);
    
    if ($callerServiceId) {
        $serviceClass = UIIdGenerator::getContextFromId($callerServiceId);
    } else {
        $serviceClass = UIIdGenerator::getContextFromId($componentId);
    }
    // ...
}
```

### Componentes Frontend

**HTML Structure** (`demo.blade.php`):
```html
<div id="menu"></div>
<div id="main"></div>
<div id="modal-overlay" class="modal-overlay hidden">
    <div id="modal" class="modal-container"></div>
</div>
```

**JavaScript** (`ui-renderer.js`):
```javascript
// Auto-detección de modales
handleUIUpdate(uiUpdate) {
    let hasModalComponents = false;
    for (const [key, component] of Object.entries(uiUpdate)) {
        if (component.parent === 'modal') {
            hasModalComponents = true;
            break;
        }
    }
    
    if (hasModalComponents) {
        openModal(uiUpdate);
        return;
    }
    
    // Manejar action: close_modal
    if (uiUpdate.action === 'close_modal') {
        closeModal();
        if (uiUpdate.ui_updates) {
            // Actualizar UI principal
            this.processUIUpdates(uiUpdate.ui_updates);
        }
    }
}

function openModal(uiData) {
    const modalContainer = document.getElementById('modal');
    modalContainer.innerHTML = '';
    
    const modalRenderer = new UIRenderer(uiData);
    modalRenderer.render();
    
    overlay.classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeModal() {
    modalContainer.innerHTML = '';
    overlay.classList.add('hidden');
    document.body.classList.remove('modal-open');
}
```

### Estilos CSS

```css
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.2s ease-out;
}

.modal-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    max-width: 90%;
    padding: 16px;
    animation: slideIn 0.3s ease-out;
    min-width: 400px;
}

body.modal-open #main,
body.modal-open #menu {
    pointer-events: none;
    filter: brightness(0.7);
}
```

### Ejemplo de Uso Completo

**Servicio que abre el modal:**
```php
public function onOpenConfirmation(array $params): array
{
    // Obtener ID del servicio actual para recibir callback
    $serviceId = $this->getServiceComponentId();
    
    // Construir diálogo
    $confirmService = app(ConfirmDialogService::class);
    $modalUI = $confirmService->getUI(
        title: "Confirm Action",
        message: "Are you sure you want to proceed?",
        icon: 'question',
        confirmAction: 'handle_confirm',
        confirmParams: ['action_type' => 'demo_action'],
        confirmLabel: 'Yes, Proceed',
        cancelAction: 'handle_cancel',
        cancelLabel: 'No, Cancel',
        callerServiceId: $serviceId
    );
    
    // El frontend detecta parent='modal' y abre overlay
    return $modalUI;
}

// Handler de confirmación (recibe callback)
public function onHandleConfirm(array $params): array
{
    // Procesar acción
    $actionType = $params['action_type'] ?? 'unknown';
    
    // Actualizar UI principal
    $updates = [];
    $updates[$labelId] = [
        'type' => 'label',
        'text' => "✅ Action confirmed!",
        'style' => 'success'
    ];
    
    // Cerrar modal y actualizar
    return [
        'action' => 'close_modal',
        'ui_updates' => $updates
    ];
}

public function onHandleCancel(array $params): array
{
    return ['action' => 'close_modal'];
}
```

### Flujo Completo

1. Usuario hace click → `onOpenConfirmation()`
2. Servicio llama `getServiceComponentId()` para obtener su ID
3. Crea modal con `ConfirmDialogService->getUI()` pasando `callerServiceId`
4. Modal especifica `parent('modal')`
5. Frontend detecta `parent: 'modal'` → llama `openModal()`
6. Usuario hace click en botón del modal
7. Request incluye `_caller_service_id`
8. `UIEventController` enruta al servicio original
9. `onHandleConfirm()` ejecuta lógica
10. Retorna `action: 'close_modal'` + `ui_updates`
11. Modal se cierra, UI principal se actualiza

---

## ☰ SISTEMA DE MENÚ DROPDOWN (Menu Dropdown)

### Componentes Backend

**MenuDropdownBuilder** (`app/Services/UI/Components/MenuDropdownBuilder.php`)
- Constructor fluido para menús dropdown con submenús anidados
- **NO hereda de UIContainer** (componente independiente)

```php
class MenuDropdownBuilder
{
    private array $config = [];
    private array $items = [];
    private string $name;

    public function __construct(string $name) { }
    
    // Agregar item con acción
    public function item(
        string $label,
        ?string $action = null,
        array $params = [],
        ?string $icon = null,
        array $submenu = []
    ): self
    
    // Agregar link de navegación
    public function link(string $label, string $url, ?string $icon = null): self
    
    // Agregar separador
    public function separator(): self
    
    // Crear submenu con callback
    public function submenu(string $label, ?string $icon = null, callable $callback): self
    {
        $submenuBuilder = new self($label . '_submenu');
        $callback($submenuBuilder);
        
        $item = [
            'label' => $label,
            'icon' => $icon,
            'submenu' => $submenuBuilder->items
        ];
        
        $this->items[] = $item;
        return $this;
    }
    
    // Especificar parent container
    public function parent(string $parentId): self
    {
        $this->config['parent'] = $parentId;
        return $this;
    }
    
    // Build con ID generado
    public function build(): array
    {
        $this->config['items'] = $this->items;
        $id = UIIdGenerator::generate($this->name);
        $this->config['_id'] = $id;
        
        return [
            $id => $this->config
        ];
    }
}
```

**UIBuilder** - Método agregado:
```php
public static function menuDropdown(string $name): MenuDropdownBuilder
{
    return new MenuDropdownBuilder($name);
}
```

### Servicio de Menú

**DemoMenuService** (`app/Services/Screens/DemoMenuService.php`):
```php
class DemoMenuService extends AbstractUIService
{
    protected function buildBaseUI(...$params): UIContainer
    {
        return new UIContainer('menu_container');
    }

    public function getUI(...$params): array
    {
        $menu = new MenuDropdownBuilder('main_menu');
        
        // IMPORTANTE: Especificar parent
        $menu->parent('menu');

        // Submenu con callback
        $menu->submenu('Demos', '🎮', function($submenu) {
            $submenu->link('Demo UI', '/demo/demo-ui', '🎨');
            $submenu->link('Table Demo', '/demo/table-demo', '📊');
            $submenu->link('Modal Demo', '/demo/modal-demo', '🪟');
        });

        $menu->separator();

        // Links directos
        $menu->link('Settings', '/demo/settings', '⚙️');
        
        return $menu->build();
    }
}
```

### Componentes Frontend

**MenuDropdownComponent** (`ui-renderer.js`):
```javascript
class MenuDropdownComponent extends UIComponent {
    render() {
        const menuContainer = document.createElement('div');
        menuContainer.className = 'menu-dropdown';
        
        const trigger = document.createElement('button');
        trigger.className = 'menu-dropdown-trigger';
        trigger.innerHTML = '☰ Menu';
        
        const content = document.createElement('div');
        content.className = 'menu-dropdown-content';
        
        // Build menu items
        this.config.items.forEach(item => {
            content.appendChild(this.renderMenuItem(item));
        });
        
        // Toggle on click
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isActive = content.classList.contains('show');
            
            // Close all menus
            document.querySelectorAll('.menu-dropdown-content.show')
                .forEach(m => m.classList.remove('show'));
            
            if (!isActive) {
                content.classList.add('show');
                trigger.classList.add('active');
            }
        });
        
        return this.applyCommonAttributes(menuContainer);
    }
    
    renderMenuItem(item) {
        if (item.type === 'separator') {
            return /* separator div */;
        }
        
        const menuItem = document.createElement(item.url ? 'a' : 'button');
        menuItem.className = 'menu-item';
        
        if (item.submenu) {
            menuItem.classList.add('has-submenu');
            // Render submenu recursively
        }
        
        if (item.url) {
            menuItem.href = item.url;
        }
        
        return menuItem;
    }
}
```

**ComponentFactory** - Agregar case:
```javascript
case 'menu_dropdown':
    return new MenuDropdownComponent(id, config);
```

**Carga automática del menú:**
```javascript
async function loadMenuUI() {
    if (!window.MENU_SERVICE) return;
    
    const response = await fetch(`/api/${window.MENU_SERVICE}/...`);
    const uiData = await response.json();
    
    const menuRenderer = new UIRenderer(uiData);
    menuRenderer.render();
}

document.addEventListener('DOMContentLoaded', () => {
    loadMenuUI();
    loadDemoUI();
});
```

### Configuración

**demo.blade.php:**
```javascript
window.MENU_SERVICE = 'demo-menu';
```

**routes/web.php:**
```php
->where('demo', '...|demo-menu')
```

**config/ui-services.php:**
```php
\App\Services\Screens\DemoMenuService::class,
```

### Estilos CSS

```css
.menu-dropdown {
    position: relative;
    display: inline-block;
    margin-bottom: 20px;
}

.menu-dropdown-trigger {
    background: white;
    border: 1px solid #dfe1e6;
    padding: 8px 16px;
    cursor: pointer;
}

.menu-dropdown-content {
    position: absolute;
    top: calc(100% + 2px);  /* Reducido para evitar gaps */
    left: 0;
    background: white;
    border: 1px solid #dfe1e6;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    z-index: 1000;
    display: none;
}

/* Pseudo-elemento para cubrir gap */
.menu-dropdown-content::before {
    content: '';
    position: absolute;
    top: -4px;
    left: 0;
    right: 0;
    height: 4px;
    background: transparent;
}

.menu-item {
    padding: 10px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
}

.menu-item:hover {
    background: #f4f5f7;
}

.submenu {
    position: absolute;
    top: 0;
    left: 100%;
    margin-left: 2px;
    min-width: 180px;
    display: none;
}

/* Pseudo-elemento para submenu gap */
.submenu::before {
    content: '';
    position: absolute;
    top: 0;
    left: -4px;
    bottom: 0;
    width: 4px;
    background: transparent;
}

.menu-item.has-submenu:hover .submenu {
    display: block;
}
```

---

## 🎨 MEJORAS AL SISTEMA BASE

### UIContainer - Métodos de estilo agregados

```php
// Sombras con niveles predefinidos
public function shadow(string|int $intensity = 1): self
{
    if (is_int($intensity)) {
        $shadows = [
            0 => 'none',
            1 => '0 2px 8px rgba(0, 0, 0, 0.1)',
            2 => '0 4px 16px rgba(0, 0, 0, 0.15)',
            3 => '0 8px 32px rgba(0, 0, 0, 0.2)',
        ];
        $shadow = $shadows[$intensity] ?? $shadows[1];
    } else {
        $shadows = [
            'light' => '0 2px 8px rgba(0, 0, 0, 0.1)',
            'medium' => '0 4px 16px rgba(0, 0, 0, 0.15)',
            'heavy' => '0 8px 32px rgba(0, 0, 0, 0.2)',
        ];
        $shadow = $shadows[$intensity] ?? $intensity;
    }
    return $this->boxShadow($shadow);
}

// Border radius simplificado
public function rounded(string|int $radius = 8): self
{
    if (is_int($radius)) {
        $radius = $radius === 0 ? '0' : "{$radius}px";
    }
    return $this->borderRadius($radius);
}

// Centrado horizontal con margin auto
public function centerHorizontal(): self
{
    $this->config['margin_left'] = 'auto';
    $this->config['margin_right'] = 'auto';
    return $this;
}

// Centrado de contenido con flexbox
public function centerContent(): self
{
    return $this->justifyContent('center')->alignItems('center');
}
```

### Frontend - applyCommonAttributes extendido

```javascript
applyCommonAttributes(element) {
    // ...existing code...
    
    // Layout properties
    if (this.config.justify_content) {
        element.style.justifyContent = this.config.justify_content;
    }
    if (this.config.align_items) {
        element.style.alignItems = this.config.align_items;
    }
    if (this.config.gap) {
        element.style.gap = this.config.gap + 'px';
    }
    
    // Padding
    if (this.config.padding !== undefined) {
        if (typeof this.config.padding === 'number') {
            element.style.padding = this.config.padding + 'px';
        } else {
            element.style.padding = this.config.padding;
        }
    }
    
    // Font size
    if (this.config.font_size) {
        element.style.fontSize = this.config.font_size + 'px';
    }
    
    return element;
}
```

---

## 🔧 PATRONES Y CONVENCIONES

### Pattern: Modal con Callback
```php
// 1. Servicio obtiene su ID
$serviceId = $this->getServiceComponentId();

// 2. Abre modal pasando callerServiceId
$modal = $confirmService->getUI(
    callerServiceId: $serviceId,
    confirmAction: 'my_action'
);

// 3. Modal incluye _caller_service_id en botones
$button->action($action, [
    '_caller_service_id' => $callerServiceId
]);

// 4. Controller enruta a servicio correcto
// 5. Servicio responde con close_modal + ui_updates
return [
    'action' => 'close_modal',
    'ui_updates' => [/* cambios */]
];
```

### Pattern: Componente sin UIContainer
```php
// Para componentes custom como MenuDropdownBuilder:
class CustomBuilder {
    private string $name;
    
    public function parent(string $parentId): self {
        $this->config['parent'] = $parentId;
        return $this;
    }
    
    public function build(): array {
        $id = UIIdGenerator::generate($this->name);
        $this->config['_id'] = $id;
        return [$id => $this->config];
    }
}
```

### Pattern: Componente en Célula de Tabla
```php
$table->row()
    ->cell('Text content')
    ->cell(
        UIBuilder::button('btn_' . $id)
            ->label('Action')
            ->action('do_something', ['id' => $id])
    );
```

### Pattern: Auto-carga de UI en div específico
```javascript
// HTML
<div id="menu"></div>

// Blade
window.MENU_SERVICE = 'demo-menu';

// JS
async function loadMenuUI() {
    const response = await fetch(`/api/${window.MENU_SERVICE}/...`);
    const uiData = await response.json();
    const menuRenderer = new UIRenderer(uiData);
    menuRenderer.render(); // Monta en parent especificado
}
```

---

## 📝 CHECKLIST DE IMPLEMENTACIÓN

Cuando crees un nuevo componente:

**Backend:**
- [ ] Crear Builder class con métodos fluidos
- [ ] Método `build()` retorna array con ID generado
- [ ] Si no hereda de UIContainer, implementar `parent()` method
- [ ] Agregar método estático en UIBuilder
- [ ] Registrar servicio en `config/ui-services.php` (si es servicio)
- [ ] Agregar ruta en `routes/web.php` (si es demo)

**Frontend:**
- [ ] Crear clase Component extends UIComponent
- [ ] Implementar método `render()` retornando HTMLElement
- [ ] Agregar case en ComponentFactory
- [ ] Definir estilos CSS en `ui-components.css`
- [ ] Si auto-carga, agregar función `load{Name}UI()`

**Testing:**
- [ ] Verificar que component ID se genera correctamente
- [ ] Verificar que `parent` se especifica (si es necesario)
- [ ] Probar eventos y callbacks
- [ ] Verificar estilos responsive

---

## 🚀 COMANDOS RÁPIDOS

```bash
# Iniciar servidor
php artisan serve

# Ver demo específico
http://localhost:8000/demo/table-demo
http://localhost:8000/demo/modal-demo

# Verificar errores PHP
# (VSCode mostrará automáticamente)

# Console browser para debug JS
# F12 → Console
```

---

## 💡 TIPS IMPORTANTES

1. **IDs Únicos:** Todos los componentes usan `UIIdGenerator::generate()` para IDs únicos y trackables
2. **Parent Required:** Componentes necesitan `parent` para montarse (excepto root containers con `parent: 'main'`)
3. **Modal Detection:** Frontend detecta automáticamente `parent: 'modal'` y activa overlay
4. **Callback Pattern:** Usar `_caller_service_id` para que modales retornen a servicio original
5. **Gap Prevention:** Usar pseudo-elementos `::before` para evitar gaps en dropdowns/submenus
6. **Type Safety:** Frontend verifica `config.type` para crear componente correcto
7. **Flexbox Default:** Containers usan flexbox, especificar `layout(LayoutType::VERTICAL/HORIZONTAL)`

---

Este documento contiene todo el conocimiento necesario para continuar el desarrollo del sistema UI. Cada patrón ha sido probado y está en producción.
