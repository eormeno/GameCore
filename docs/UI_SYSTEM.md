# Sistema UI Builder - Documentación Completa

**Fecha:** 20 de octubre de 2025  
**Versión:** 2.0 (Sistema de Parent)

---

## Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Arquitectura](#arquitectura)
3. [Sistema de IDs](#sistema-de-ids)
4. [Sistema de Parent (Jerarquía)](#sistema-de-parent)
5. [Componentes Base](#componentes-base)
6. [Tablas](#tablas)
7. [Contenedores](#contenedores)
8. [API Rápida](#api-rápida)
9. [Migración de Slot a Parent](#migración-de-slot-a-parent)
10. [Ejemplos Completos](#ejemplos-completos)

---

## Visión General

El **UI Builder** es un sistema para construir interfaces de usuario dinámicas mediante PHP que genera estructuras JSON. El cliente (frontend) recibe este JSON y renderiza los componentes.

### Características Principales

- ✅ **IDs numéricos auto-incrementales** únicos por sesión
- ✅ **Sistema de parent** para jerarquías padre-hijo
- ✅ **API fluida** para construcción de componentes
- ✅ **Arquitectura de árbol** con componentes anidados
- ✅ **Serialización JSON** plana y eficiente
- ✅ **Type safety** con enums y tipos PHP

---

## Arquitectura

### Estructura de Clases

```
UIElement (Interface)
├── UIComponent (Abstract)
│   ├── ButtonBuilder
│   ├── LabelBuilder
│   ├── InputBuilder
│   ├── TableBuilder
│   ├── TableRowBuilder
│   ├── TableCellBuilder
│   ├── TableHeaderRowBuilder
│   └── TableHeaderCellBuilder
└── UIContainer (Composite)
```

### Flujo de Construcción

```
1. Crear componente con UIBuilder
2. Configurar propiedades (fluent API)
3. Establecer jerarquía (parent)
4. Llamar a toJson() para serializar
5. Enviar JSON al cliente
```

---

## Sistema de IDs

### UIIdGenerator

Genera IDs numéricos únicos por sesión:

```php
UIIdGenerator::generate(); // 32600001
UIIdGenerator::generate(); // 32600002
UIIdGenerator::generate(); // 32600003
```

**Características:**
- IDs numéricos secuenciales
- Únicos por sesión de usuario
- Rango: 32600000 + incremento
- Reset al iniciar nueva sesión

**Evitar:**
- ❌ IDs basados en strings con sufijos (ej: `"123:button"`)
- ❌ IDs manuales duplicados

---

## Sistema de Parent (Jerarquía)

### Concepto

El **parent** establece la relación padre-hijo entre componentes. Cada componente puede tener un parent (excepto el root).

### API

```php
// Establecer parent por ID
$button->parent(1); // El parent es el componente con ID 1

// Establecer parent por nombre (para root)
$container->parent('canvas'); // El parent es un slot del cliente llamado "canvas"

// Sin parent (componente root)
$component->parent(null);
```

### En JSON

```json
{
    "1": {
        "type": "container",
        "parent": "canvas"
    },
    "2": {
        "type": "button",
        "parent": 1
    }
}
```

### Seteo Automático

Los contenedores establecen automáticamente el parent de sus hijos:

```php
$container = UIBuilder::container();
$container->add(UIBuilder::button()); // parent se establece automáticamente
```

---

## Componentes Base

### UIComponent

Clase abstracta base para componentes leaf (sin hijos).

**Propiedades Comunes:**
- `id`: ID numérico único
- `parent`: ID del parent o nombre de slot
- `name`: Nombre opcional del componente
- `visible`: Visibilidad del componente

**Métodos:**
```php
$component->parent(int|string|null $parent)
$component->name(string $name)
$component->visible(bool $visible)
$component->toJson(): array
```

### UIContainer

Contenedor que puede tener múltiples hijos (patrón Composite).

**Características:**
- Maneja colección de hijos
- Setea automáticamente parent de hijos
- Serialización recursiva

**Métodos:**
```php
$container->add(UIElement $element)
$container->addMany(array $elements)
$container->remove(int $elementId)
$container->find(int $elementId): ?UIElement
$container->getChildren(): array
$container->clear()
```

---

## Tablas

### Arquitectura de Tablas

```
TableBuilder
├── header_row (TableHeaderRowBuilder) - Opcional
│   └── TableHeaderCellBuilder[] - Celdas de encabezado
└── rows_container (UIContainer)
    └── TableRowBuilder[] - Filas de datos
        └── TableCellBuilder[] - Celdas de datos
```

### Crear Tabla con Headers

```php
$table = UIBuilder::table('users_table')
    ->title('User Management')
    ->minRows(5);

// Crear header row
$headerRow = $table->createHeaderRow();
$headerRow->createCell()->text('#')->sortable(true);
$headerRow->createCell()->text('Name')->sortable(true)->sortDirection('asc');
$headerRow->createCell()->text('Email')->width('300px');
$headerRow->createCell()->text('Role')->align(TextAlign::CENTER);

// Agregar filas de datos
$row = $table->createRow();
$row->cells([1, 'John Doe', 'john@example.com', 'Admin']);

return $table->toJson();
```

### TableHeaderCellBuilder

**Propiedades:**
- `text`: Texto del header
- `sortable`: Si permite ordenamiento (bool)
- `sort_direction`: Dirección inicial ('asc' | 'desc' | null)
- `align`: Alineación del texto
- `width`: Ancho de columna
- `action`: Acción al hacer click (para sorting)
- `tooltip`: Texto de ayuda
- `color`: Color del texto
- `backgroundColor`: Color de fondo
- `fontWeight`: Peso de la fuente
- `colspan`: Número de columnas que ocupa (default: 1)

**Ejemplo Avanzado:**
```php
$headerRow->createCell()
    ->text('Sales')
    ->sortable(true)
    ->sortDirection('desc')
    ->action('sort_column', ['column' => 'sales'])
    ->tooltip('Click to sort by sales')
    ->align(TextAlign::RIGHT)
    ->width('150px')
    ->color('#007bff')
    ->fontWeight(FontWeight::BOLD)
    ->colspan(2); // Ocupa 2 columnas
```

### Auto-fill con MinRows

Si defines `minRows`, la tabla se auto-completa con filas vacías:

```php
$table->minRows(10); // Si hay 3 filas con datos, agrega 7 vacías
```

### JSON de Tabla

```json
{
    "1": {
        "type": "table",
        "title": "Users",
        "header_row": 3,
        "rows_container": 2,
        "min_rows": 5,
        "name": "users_table"
    },
    "2": {
        "type": "container",
        "parent": 1,
        "layout": "vertical",
        "name": "rows"
    },
    "3": {
        "type": "tableheaderrow",
        "parent": 1
    },
    "4": {
        "type": "tableheadercell",
        "parent": 3,
        "text": "#",
        "sortable": true
    }
}
```

---

## Contenedores

### Crear Contenedor

```php
$container = UIBuilder::container('main_content')
    ->parent('canvas')
    ->layout(LayoutType::VERTICAL)
    ->title('Dashboard');
```

### Layouts

```php
enum LayoutType {
    case VERTICAL;   // Apilar verticalmente
    case HORIZONTAL; // Apilar horizontalmente
    case GRID;       // Grid layout
    case FLEX;       // Flexbox
}
```

### Agregar Componentes

```php
$container->add(UIBuilder::button('save')->label('Save'));
$container->add(UIBuilder::label()->text('Hello World'));

// O múltiples
$container->addMany([
    UIBuilder::button('cancel')->label('Cancel'),
    UIBuilder::input('username')->placeholder('Username'),
]);
```

---

## API Rápida

### UIBuilder Factory

```php
// Contenedor
UIBuilder::container(?string $name = null): ContainerBuilder

// Botón
UIBuilder::button(?string $name = null): ButtonBuilder

// Etiqueta
UIBuilder::label(?string $name = null): LabelBuilder

// Input
UIBuilder::input(?string $name = null): InputBuilder

// Tabla
UIBuilder::table(?string $name = null): TableBuilder
```

### Button

```php
$button = UIBuilder::button('save_button')
    ->label('Save Changes')
    ->action('save_data', ['id' => 123])
    ->icon('save')
    ->style('primary')
    ->variant('solid')
    ->enabled(true)
    ->tooltip('Click to save');
```

### Label

```php
$label = UIBuilder::label()
    ->text('Welcome back!')
    ->style('success')
    ->fontSize('18px')
    ->fontWeight(FontWeight::BOLD);
```

### Input

```php
$input = UIBuilder::input('email')
    ->type('email')
    ->placeholder('Enter your email')
    ->value('user@example.com')
    ->required(true)
    ->disabled(false);
```

---

## Migración de Slot a Parent

### Cambios Realizados

El sistema migró de usar "slot" a "parent" para mayor claridad semántica:

| Antes (Slot) | Después (Parent) |
|--------------|------------------|
| `$component->slot(1)` | `$component->parent(1)` |
| `getSlot()` | `getParent()` |
| `setSlot()` | `setParent()` |
| `"slot": 1` (JSON) | `"parent": 1` (JSON) |

### Guía de Migración

**1. Actualizar llamadas en código:**
```php
// ❌ Antiguo
$container->slot('canvas');

// ✅ Nuevo
$container->parent('canvas');
```

**2. Actualizar referencias en frontend:**
```javascript
// ❌ Antiguo
const parentId = component.slot;

// ✅ Nuevo
const parentId = component.parent;
```

**3. Interfaces actualizadas:**
```php
interface UIElement {
    public function getParent(): int|string|null;
    public function setParent(int|string|null $parent): void;
}
```

### Breaking Change

⚠️ **Advertencia:** Esta es una breaking change. Todo código que use `slot` debe actualizarse a `parent`.

---

## Ejemplos Completos

### Ejemplo 1: Pantalla de Login

```php
$screen = UIBuilder::container('login_screen')
    ->parent('canvas')
    ->layout(LayoutType::VERTICAL)
    ->title('Login');

$screen->add(
    UIBuilder::label()
        ->text('Welcome back!')
        ->fontSize('24px')
);

$screen->add(
    UIBuilder::input('username')
        ->type('text')
        ->placeholder('Username')
        ->required(true)
);

$screen->add(
    UIBuilder::input('password')
        ->type('password')
        ->placeholder('Password')
        ->required(true)
);

$screen->add(
    UIBuilder::button('login_btn')
        ->label('Login')
        ->action('login')
        ->style('primary')
);

return $screen->toJson();
```

### Ejemplo 2: Tabla de Usuarios con Headers

```php
$table = UIBuilder::table('users_table')
    ->title('Users List')
    ->minRows(10);

// Header
$header = $table->createHeaderRow();
$header->createCell()->text('ID')->sortable(true)->width('50px');
$header->createCell()->text('Name')->sortable(true)->sortDirection('asc');
$header->createCell()->text('Email')->width('250px');
$header->createCell()->text('Role');
$header->createCell()->text('Actions')->colspan(2);

// Datos
$users = [
    ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'role' => 'Admin'],
    ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com', 'role' => 'User'],
];

foreach ($users as $user) {
    $row = $table->createRow();
    $row->createCell()->text($user['id']);
    $row->createCell()->text($user['name']);
    $row->createCell()->text($user['email']);
    $row->createCell()->text($user['role']);
    
    // Botones de acción en la misma celda
    $actionsCell = $row->createCell();
    $actionsCell->addChild(
        UIBuilder::button()->label('Edit')->action('edit', ['id' => $user['id']])
    );
    $actionsCell->addChild(
        UIBuilder::button()->label('Delete')->action('delete', ['id' => $user['id']])
    );
}

return $table->toJson();
```

### Ejemplo 3: Dashboard con Múltiples Secciones

```php
$dashboard = UIBuilder::container('dashboard')
    ->parent('canvas')
    ->layout(LayoutType::VERTICAL)
    ->title('Dashboard');

// Header section
$header = UIBuilder::container('header')
    ->layout(LayoutType::HORIZONTAL);
$header->add(UIBuilder::label()->text('Welcome, Admin')->fontSize('20px'));
$header->add(UIBuilder::button('logout')->label('Logout')->style('danger'));

// Stats section
$stats = UIBuilder::container('stats')
    ->layout(LayoutType::GRID);
$stats->add(UIBuilder::label()->text('Users: 150'));
$stats->add(UIBuilder::label()->text('Sales: $10,000'));
$stats->add(UIBuilder::label()->text('Orders: 45'));

// Table section
$table = UIBuilder::table('recent_orders')
    ->title('Recent Orders')
    ->minRows(5);

$headerRow = $table->createHeaderRow();
$headerRow->createCell()->text('Order #');
$headerRow->createCell()->text('Customer');
$headerRow->createCell()->text('Total');
$headerRow->createCell()->text('Status');

// Ensamblar todo
$dashboard->add($header);
$dashboard->add($stats);
$dashboard->add($table);

return $dashboard->toJson();
```

---

## Best Practices

### ✅ DO

- Usar IDs numéricos generados automáticamente
- Establecer parent explícitamente para componentes root
- Usar nombres descriptivos para componentes importantes
- Aprovechar el auto-fill con `minRows` en tablas
- Usar enums para tipos (TextAlign, LayoutType, etc.)
- Aprovechar la API fluida para código limpio

### ❌ DON'T

- No crear IDs manualmente
- No usar strings con sufijos tipo `:button`
- No modificar el config directamente (usar métodos)
- No usar el método deprecated `addHeader()` (usar `createHeaderRow()`)
- No mezclar sistema viejo (slot) con nuevo (parent)

---

## Troubleshooting

### Error: "Call to undefined method slot()"

**Causa:** Intentando usar la API antigua.  
**Solución:** Cambiar `slot()` por `parent()`.

### Error: "Only one header row is allowed"

**Causa:** Intentando crear múltiples header rows.  
**Solución:** Una tabla solo puede tener un header row. Usa `getHeaderRow()` para obtener el existente.

### Filas vacías no aparecen

**Causa:** No se estableció `minRows`.  
**Solución:** Usa `$table->minRows(n)` para auto-completar.

---

## Changelog

### v2.0 - Sistema Parent (2025-10-20)

- ✅ Migración completa de "slot" a "parent"
- ✅ Eliminados arrays redundantes `headers` y `rows` en tablas
- ✅ TableHeaderRowBuilder y TableHeaderCellBuilder implementados
- ✅ Método `addHeader()` marcado como deprecated
- ✅ Colspan support en header cells
- ✅ Documentación unificada

### v1.0 - Sistema Base

- IDs numéricos auto-incrementales
- Arquitectura de componentes
- Sistema de slots
- Contenedores y componentes básicos
