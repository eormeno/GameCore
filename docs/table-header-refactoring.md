# Refactoring del Sistema de Headers en TableBuilder

**Fecha:** 20 de octubre de 2025  
**Branch:** table-header  
**Estado:** ✅ Completado

## Resumen

Se ha refactorizado completamente el sistema de headers de las tablas, pasando de un sistema basado en arrays planos con IDs string a un sistema de componentes con IDs numéricos auto-incrementales, consistente con el resto del framework UI.

## Componentes Nuevos

### 1. TableHeaderCellBuilder

**Archivo:** `app/Services/UI/Components/TableHeaderCellBuilder.php`

Representa una celda individual del header de la tabla.

**Propiedades:**
- `text`: string - Texto del header
- `sortable`: bool (default: false) - Si es ordenable
- `sort_direction`: string|null - Dirección del ordenamiento ('asc', 'desc', null)
- `align`: string|null - Alineación (left, center, right)
- `width`: string|null - Ancho de la columna (e.g., '200px', '20%')
- `action`: string|null - Acción a disparar al hacer clic (para ordenamiento)
- `tooltip`: string|null - Texto del tooltip
- `color`: string|null - Color del texto
- `background_color`: string|null - Color de fondo
- `font_weight`: string (default: 'bold') - Peso de la fuente
- `colspan`: int (default: 1) - Cuántas columnas ocupa

**Métodos principales:**
```php
$cell->text(string $text): self
$cell->sortable(bool $sortable = true): self
$cell->sortDirection(?string $direction): self  // 'asc', 'desc', null
$cell->align(Align $align): self
$cell->width(string $width): self
$cell->action(string $action): self
$cell->tooltip(string $tooltip): self
$cell->color(string $color): self
$cell->backgroundColor(string $backgroundColor): self
$cell->fontWeight(FontWeight $fontWeight): self
$cell->colspan(int $span): self  // Mínimo 1
```

### 2. TableHeaderRowBuilder

**Archivo:** `app/Services/UI/Components/TableHeaderRowBuilder.php`

Representa la fila de headers de la tabla.

**Propiedades:**
- Contiene un array de `TableHeaderCellBuilder`
- Referencia al `TableBuilder` padre

**Métodos principales:**
```php
$headerRow->createCell(?string $name = null): TableHeaderCellBuilder
$headerRow->addCell(TableHeaderCellBuilder $cell): self
$headerRow->getCells(): array<TableHeaderCellBuilder>
$headerRow->getTable(): TableBuilder
```

## Modificaciones en TableBuilder

**Archivo:** `app/Services/UI/Components/TableBuilder.php`

**Nuevas propiedades:**
- `private ?TableHeaderRowBuilder $headerRow = null` - Referencia opcional al header row

**Nuevos métodos:**
```php
// Crear el header row (solo se permite uno por tabla)
$table->createHeaderRow(?string $name = null): TableHeaderRowBuilder

// Obtener el header row si existe
$table->getHeaderRow(): ?TableHeaderRowBuilder
```

**Métodos deprecados:**
```php
// @deprecated Use createHeaderRow()->createCell() instead
$table->addHeader(...): self

// @deprecated Use createHeaderRow()->createCell() instead
$table->headers(array $headers): self
```

**Cambios en toJson():**
- Ahora incluye el `header_row` en el JSON si existe
- El header_row se serializa junto con todas sus celdas en la estructura plana

## Estructura JSON Resultante

### Sistema Nuevo (Componentes)

```json
{
  "1": {
    "type": "table",
    "title": "Users",
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
    "sortable": true,
    "sort_direction": "asc",
    "action": "sort_by_name",
    "slot": 3
  },
  "5": {
    "type": "tableheadercell",
    "text": "Email",
    "sortable": true,
    "action": "sort_by_email",
    "colspan": 2,
    "slot": 3
  }
}
```

### Sistema Antiguo (Arrays) - Aún funcional

```json
{
  "1": {
    "type": "table",
    "headers": {
      "name_header:tableheader": {
        "text": "Name",
        "sortable": false,
        "align": "center"
      }
    }
  }
}
```

## Ejemplo de Uso

### Método Nuevo (Recomendado)

```php
$table = UIBuilder::table('users_table')
    ->title('User Management');

// Crear header row
$headerRow = $table->createHeaderRow();

// Agregar celdas de header
$headerRow->createCell()->text('ID')->align(Align::CENTER);

$headerRow->createCell()
    ->text('Name')
    ->sortable(true)
    ->sortDirection('asc')
    ->action('sort_by_name')
    ->tooltip('Click to sort by name');

$headerRow->createCell()
    ->text('User Info')
    ->colspan(2)
    ->align(Align::CENTER);

// Agregar filas de datos
$table->createRow()->cells([1, 'John', 'john@example.com']);
```

### Método Antiguo (Deprecado pero funcional)

```php
$table = UIBuilder::table('users_table')
    ->title('User Management')
    ->addHeader('ID')
    ->addHeader('Name', sortable: true)
    ->addHeader('Email');
```

## Actualización de GameLobbyScreenService

**Archivo:** `app/Services/Screens/GameLobbyScreenService.php`

**Antes:**
```php
$table = UIBuilder::table()
    ->addHeader(t('games.saved_game_number_column'))
    ->addHeader(t('games.saved_game_name_column'))
    // ... más headers
```

**Después:**
```php
$table = UIBuilder::table();

$headerRow = $table->createHeaderRow();
$headerRow->createCell()->text(t('games.saved_game_number_column'));
$headerRow->createCell()->text(t('games.saved_game_name_column'));
// ... más celdas
```

## Tests

**Archivo:** `tests/Unit/Services/UI/TableHeaderTest.php`

Se crearon 13 tests que cubren:
- ✅ Creación de header row
- ✅ Restricción de un solo header row por tabla
- ✅ Creación de header cells con texto
- ✅ Configuración de sortable y action
- ✅ Configuración de colspan
- ✅ Validación de colspan mínimo
- ✅ Opciones de estilo (align, width, color, etc.)
- ✅ Validación de sort_direction
- ✅ Inclusión del header row en toJson()
- ✅ Referencias correctas de slots
- ✅ Filtrado de valores por defecto en JSON
- ✅ Tabla completa con header y datos

**Resultados:**
```
Tests:    13 passed (36 assertions)
Duration: 0.48s
```

**Suite completa de UI:**
```
Tests:    1 skipped, 71 passed (222 assertions)
Duration: 0.68s
```

## Características Principales

### 1. IDs Numéricos Auto-incrementales
- Consistente con el resto del framework UI
- Usa `UIIdGenerator` para generación centralizada
- IDs únicos y predecibles en tests

### 2. Sistema de Slots
- `TableHeaderRow` tiene slot apuntando a `TableBuilder`
- `TableHeaderCell` tiene slot apuntando a `TableHeaderRowBuilder`
- Estructura jerárquica clara

### 3. Headers como Botones Toggle
- Propiedad `sortable` para indicar si se puede ordenar
- Propiedad `action` para disparar eventos de ordenamiento
- Soporte para `sort_direction` (asc/desc)

### 4. Colspan para Headers
- Propiedad `colspan` permite headers que ocupan múltiples columnas
- Validación: mínimo 1
- Default: 1 (se omite del JSON)

### 5. Compatibilidad Hacia Atrás
- El método antiguo `addHeader()` sigue funcionando
- Marcado como `@deprecated` para migración gradual
- No rompe código existente

### 6. Filtrado Inteligente de JSON
- Se omiten valores por defecto:
  - `visible: true`
  - `sortable: false`
  - `colspan: 1`
  - `font_weight: 'bold'`
- JSON más limpio y compacto

## Beneficios del Refactoring

1. **Consistencia**: Todo usa IDs numéricos y el sistema de componentes
2. **Flexibilidad**: Headers pueden tener acciones, tooltips, estilos personalizados
3. **Extensibilidad**: Fácil agregar nuevas propiedades a las celdas
4. **Type Safety**: Uso de Enums (Align, FontWeight) en lugar de strings
5. **Testabilidad**: Componentes independientes, fáciles de probar
6. **Mantenibilidad**: Menos parámetros en métodos, API más clara
7. **Colspan**: Soporte nativo para headers que ocupan múltiples columnas
8. **Interactividad**: Headers pueden ser clickeables con acciones

## Archivos Creados/Modificados

### Creados:
- `app/Services/UI/Components/TableHeaderCellBuilder.php`
- `app/Services/UI/Components/TableHeaderRowBuilder.php`
- `tests/Unit/Services/UI/TableHeaderTest.php`
- `test_new_header_system.php` (demo)

### Modificados:
- `app/Services/UI/Components/TableBuilder.php`
- `app/Services/Screens/GameLobbyScreenService.php`

### Sin cambios (compatibilidad):
- Todos los tests antiguos siguen pasando
- `test_table_headers.php` sigue funcionando con `addHeader()`

## Próximos Pasos (Opcionales)

1. **Migración gradual**: Actualizar otros servicios que usen `addHeader()`
2. **Deprecation warnings**: Agregar logs cuando se use el método antiguo
3. **Documentación**: Actualizar guías de usuario del framework
4. **Ejemplos**: Crear más ejemplos de uso avanzado (sorting, filtering)
5. **Cliente**: Actualizar el renderizador del frontend para los nuevos tipos

## Notas Técnicas

- El campo `headers` en `TableBuilder` se mantiene para compatibilidad pero está deprecado
- El nuevo sistema usa `header_row` en lugar de `headers`
- Los tipos de componente son `tableheaderrow` y `tableheadercell`
- El sistema antiguo usa sufijo `:tableheader` en los IDs string
- Ambos sistemas pueden coexistir temporalmente
