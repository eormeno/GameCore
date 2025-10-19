# TableBuilder - Atributo minRows

## Descripción

El atributo `minRows` permite establecer un número mínimo de filas que se deben mostrar en la tabla. Si la tabla tiene menos filas de datos que el valor especificado, se rellenará automáticamente con filas vacías hasta alcanzar el mínimo.

## Uso

### Sintaxis

```php
$table->minRows(int $minRows): self
```

### Parámetros

- **`$minRows`** (int): Número mínimo de filas a mostrar en la tabla

### Valor por Defecto

- `null` - No se establece un mínimo (la tabla mostrará solo las filas con datos)
- Cuando es `null`, no aparece en el JSON (se filtra automáticamente)

## Ejemplos

### Ejemplo 1: Tabla Básica con minRows

```php
$table = UIBuilder::table('products')
    ->title('Products')
    ->addHeader('ID')
    ->addHeader('Name')
    ->addHeader('Price')
    ->minRows(10);  // Siempre mostrará al menos 10 filas
```

**JSON generado:**
```json
{
    "1": {
        "type": "table",
        "title": "Products",
        "headers": { ... },
        "min_rows": 10,
        "pagination": false
    }
}
```

### Ejemplo 2: Combinado con Pagination

```php
$table = UIBuilder::table('orders')
    ->title('Recent Orders')
    ->addHeader('Order #')
    ->addHeader('Customer')
    ->addHeader('Total')
    ->addHeader('Status')
    ->minRows(15)
    ->pagination(true);
```

### Ejemplo 3: Tabla de Juegos Guardados (Caso Real)

```php
$savedGames = UIBuilder::table('saved_games')
    ->title('Saved Games')
    ->addHeader('#')
    ->addHeader('Game Name')
    ->addHeader('State')
    ->addHeader('Role')
    ->addHeader('Status')
    ->addHeader('Last Played')
    ->addHeader('Actions', width: '200px')
    ->minRows(5);  // Siempre muestra 5 filas para consistencia visual

// Agregar filas dinámicamente
foreach ($games as $index => $game) {
    $row = $savedGames->createRow("saved_game_{$game->id}_row");
    $row->cells([
        $index + 1,
        $game->name,
        $game->state,
        $game->role,
        $game->status,
        $game->last_played,
        $actionsContainer  // Contenedor con botones
    ]);
    $savedGames->addRow($row);
}

// Si hay solo 2 juegos, se rellenarán 3 filas vacías automáticamente
```

### Ejemplo 4: Tabla Vacía con Estructura Fija

```php
// Tabla que siempre muestra una estructura fija, incluso sin datos
$leaderboard = UIBuilder::table('leaderboard')
    ->title('Top 10 Players')
    ->addHeader('Rank', width: '60px', align: TextAlign::CENTER)
    ->addHeader('Player', width: '200px', align: TextAlign::LEFT)
    ->addHeader('Score', width: '100px', align: TextAlign::RIGHT)
    ->addHeader('Wins', width: '80px', align: TextAlign::CENTER)
    ->minRows(10);  // Siempre muestra 10 posiciones

// Incluso sin datos, la tabla tendrá 10 filas vacías
```

## Casos de Uso

### ✅ **Cuándo Usar minRows**

1. **Consistencia Visual**: Mantener el mismo tamaño de tabla en diferentes estados
2. **Placeholders**: Mostrar estructura antes de cargar datos
3. **Rankings/Leaderboards**: Mostrar posiciones fijas (Top 10, Top 20, etc.)
4. **Formularios Tabulares**: Espacios para completar datos
5. **Listas de Espera**: Mostrar slots disponibles

### ❌ **Cuándo NO Usar minRows**

1. **Tablas Dinámicas Grandes**: Cuando la cantidad de datos es variable e impredecible
2. **Búsquedas/Filtros**: Donde el número de resultados varía significativamente
3. **Tablas con Scroll Infinito**: Donde se cargan datos bajo demanda

## Comportamiento

### Con Datos Suficientes
```php
$table->minRows(5);
// Si hay 7 filas de datos → Se muestran las 7 filas (no se limita)
```

### Con Datos Insuficientes
```php
$table->minRows(5);
// Si hay 2 filas de datos → Se muestran 2 filas + 3 filas vacías = 5 total
```

### Sin minRows
```php
// Si no se especifica minRows
// Se muestran solo las filas con datos (comportamiento predeterminado)
```

## Integración con Otras Características

### minRows + Pagination

```php
$table = UIBuilder::table('data')
    ->minRows(20)      // Mínimo 20 filas por página
    ->pagination(true); // Con paginación activada
```

### minRows + Sorting

```php
$table = UIBuilder::table('data')
    ->addHeader('Name', sortable: true)
    ->addHeader('Age', sortable: true)
    ->minRows(15);  // Mantiene 15 filas incluso al ordenar
```

### minRows + Custom Styling

```php
$table = UIBuilder::table('styled_table')
    ->title('Employee List')
    ->addHeader('ID')
    ->addHeader('Name')
    ->addHeader('Department')
    ->minRows(10)
    ->padding('1rem')
    ->rounded('8px')
    ->shadow('medium');
```

## Ejemplo Completo: Game Lobby

```php
public function buildSavedGamesTable(array $games): TableBuilder
{
    $table = UIBuilder::table('saved_games_table')
        ->title('Your Saved Games')
        ->addHeader('#', width: '50px', align: TextAlign::CENTER)
        ->addHeader('Game Name', width: '200px', align: TextAlign::LEFT)
        ->addHeader('State', width: '100px', align: TextAlign::CENTER)
        ->addHeader('Role', width: '100px', align: TextAlign::CENTER)
        ->addHeader('Status', width: '100px', align: TextAlign::CENTER)
        ->addHeader('Join Method', width: '120px', align: TextAlign::CENTER)
        ->addHeader('Last Played', width: '150px', align: TextAlign::CENTER)
        ->addHeader('Actions', width: '200px', align: TextAlign::CENTER)
        ->minRows(5)  // Siempre muestra 5 slots
        ->pagination(false);

    // Agregar juegos existentes
    foreach ($games as $index => $game) {
        $actionsContainer = $this->buildGameActions($game);
        
        $row = $table->createRow("saved_game_{$game->id}_row");
        $row->cells([
            $index + 1,
            $game->name ?? 'Untitled Game',
            $game->state,
            $game->role,
            $game->status,
            $game->join_method,
            $game->last_played,
            $actionsContainer->toJson()
        ]);
        
        $table->addRow($row);
    }

    // Si hay menos de 5 juegos, se rellenan automáticamente con filas vacías
    return $table;
}
```

## JSON Output

### Tabla con minRows Establecido

```json
{
    "1": {
        "type": "table",
        "visible": true,
        "title": "Products",
        "headers": {
            "id_header:tableheader": {
                "visible": true,
                "text": "ID",
                "sortable": false,
                "align": "center",
                "font_weight": "bold"
            }
        },
        "rows": [],
        "pagination": false,
        "min_rows": 10,
        "name": "products"
    }
}
```

### Tabla sin minRows (Valor por Defecto)

```json
{
    "1": {
        "type": "table",
        "visible": true,
        "title": "Products",
        "headers": { ... },
        "rows": [],
        "pagination": false,
        "name": "products"
        // min_rows no aparece porque es null
    }
}
```

## Notas Técnicas

1. **Type Safety**: El parámetro es `int`, no acepta valores negativos en tiempo de ejecución
2. **Null Filtering**: Cuando es `null`, se filtra automáticamente del JSON
3. **Client-Side**: La lógica de relleno de filas vacías se implementa en el cliente
4. **Performance**: No afecta el rendimiento del servidor, solo es metadata

## Compatibilidad

- ✅ Compatible con todas las características existentes de TableBuilder
- ✅ Se combina con `pagination()`, `title()`, `addHeader()`, etc.
- ✅ Funciona con TableRowBuilder y componentes anidados
- ✅ No rompe código existente (valor por defecto `null`)

## Changelog

**Octubre 19, 2025**
- ✅ Agregado atributo `minRows` opcional
- ✅ Método fluido `minRows(int $minRows)`
- ✅ Filtrado automático de valores null
- ✅ Documentación completa
