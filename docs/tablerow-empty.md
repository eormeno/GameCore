# TableRowBuilder - Atributo Empty

## Descripción

El atributo `empty` permite marcar una fila de tabla como vacía o placeholder. Es especialmente útil cuando se usa `minRows` para rellenar tablas con filas vacías hasta alcanzar el mínimo requerido.

## Uso

### Sintaxis

```php
$row->empty(bool $empty = true): self
```

### Parámetros

- **`$empty`** (bool): `true` si la fila está vacía, `false` si tiene datos (default: `true`)

### Valor por Defecto

- `null` - La fila no está marcada como vacía
- Cuando es `null`, no aparece en el JSON (se filtra automáticamente)

## Ejemplos

### Ejemplo 1: Fila Normal vs Fila Vacía

```php
$table = UIBuilder::table('users')
    ->addHeader('ID')
    ->addHeader('Name')
    ->addHeader('Email');

// Fila con datos (no se marca como empty)
$dataRow = $table->createRow('user_1');
$dataRow->cells([1, 'John Doe', 'john@example.com']);
$table->addRow($dataRow);

// Fila vacía (marcada explícitamente)
$emptyRow = $table->createRow('empty_row');
$emptyRow->cells(['', '', ''])
    ->empty(true);
$table->addRow($emptyRow);
```

**JSON generado:**
```json
{
    "user_1": {
        "type": "tablerow",
        "cells": [1, "John Doe", "john@example.com"],
        "selected": false,
        "style": "default"
        // empty no aparece (es null)
    },
    "empty_row": {
        "type": "tablerow",
        "cells": ["", "", ""],
        "selected": false,
        "style": "default",
        "empty": true  // Marcada como vacía
    }
}
```

### Ejemplo 2: Completar minRows con Filas Vacías

```php
$table = UIBuilder::table('orders')
    ->title('Recent Orders')
    ->addHeader('Order #')
    ->addHeader('Customer')
    ->addHeader('Total')
    ->minRows(5);

// Agregar 2 órdenes reales
$orders = [
    ['Order' => 1001, 'Customer' => 'Alice', 'Total' => '$150'],
    ['Order' => 1002, 'Customer' => 'Bob', 'Total' => '$220'],
];

foreach ($orders as $index => $order) {
    $row = $table->createRow("order_$index");
    $row->cells([$order['Order'], $order['Customer'], $order['Total']]);
    $table->addRow($row);
}

// Completar con filas vacías hasta alcanzar minRows (5)
$currentRows = count($orders);
$minRows = 5;

for ($i = $currentRows; $i < $minRows; $i++) {
    $emptyRow = $table->createRow("empty_row_$i");
    $emptyRow->cells(['', '', ''])
        ->empty(true);  // Marcar como vacía
    $table->addRow($emptyRow);
}

// Resultado: 2 filas con datos + 3 filas vacías = 5 total
```

### Ejemplo 3: Fila Vacía con Estilo Personalizado

```php
$table = UIBuilder::table('leaderboard')
    ->title('Top 10 Players')
    ->addHeader('Rank')
    ->addHeader('Player')
    ->addHeader('Score')
    ->minRows(10);

// Top player con estilo especial
$winner = $table->createRow('rank_1');
$winner->cells([1, 'ProGamer123', '9,850'])
    ->style('success');
$table->addRow($winner);

// Slots vacíos con estilo diferente
for ($i = 2; $i <= 10; $i++) {
    $emptySlot = $table->createRow("rank_$i");
    $emptySlot->cells([$i, '-', '-'])
        ->empty(true)
        ->style('muted');  // Estilo atenuado para slots vacíos
    $table->addRow($emptySlot);
}
```

### Ejemplo 4: Game Lobby - Saved Games Table

```php
public function buildSavedGamesTable(array $games): TableBuilder
{
    $table = UIBuilder::table('saved_games')
        ->title('Your Saved Games')
        ->addHeader('#', width: '50px')
        ->addHeader('Game Name', width: '200px')
        ->addHeader('State', width: '100px')
        ->addHeader('Status', width: '100px')
        ->addHeader('Last Played', width: '150px')
        ->addHeader('Actions', width: '200px')
        ->minRows(5);

    // Agregar juegos guardados
    foreach ($games as $index => $game) {
        $actionsContainer = $this->buildGameActions($game);
        
        $row = $table->createRow("saved_game_{$game->id}_row");
        $row->cells([
            $index + 1,
            $game->name ?? 'Untitled Game',
            $game->state,
            $game->status,
            $game->last_played,
            $actionsContainer->toJson()
        ]);
        
        $table->addRow($row);
    }

    // Rellenar con filas vacías si hay menos de 5 juegos
    $gameCount = count($games);
    if ($gameCount < 5) {
        for ($i = $gameCount + 1; $i <= 5; $i++) {
            $emptyRow = $table->createRow("empty_row_$i");
            $emptyRow->cells([$i, '', '', '', '', ''])
                ->empty(true)
                ->style('default');
            $table->addRow($emptyRow);
        }
    }

    return $table;
}
```

## Casos de Uso

### ✅ **Cuándo Usar empty**

1. **Completar minRows**: Rellenar tabla hasta alcanzar el mínimo de filas
2. **Placeholders Visuales**: Mostrar slots disponibles en rankings/leaderboards
3. **Aplicar Estilos Diferentes**: El cliente puede estilizar filas vacías de forma distinta
4. **Indicar Disponibilidad**: En listas de espera, slots de jugadores, etc.
5. **UX Consistente**: Mantener el mismo alto de tabla independientemente de los datos

### ❌ **Cuándo NO Usar empty**

1. **Filas con Datos**: No marcar como empty si tiene información real
2. **Tablas Dinámicas**: Si el tamaño varía y no hay minRows establecido
3. **Sin Propósito Visual**: Si no hay diferencia en el renderizado

## Combinación con Otros Atributos

### empty + style

```php
$emptyRow = $table->createRow('empty_1')
    ->cells(['', '', ''])
    ->empty(true)
    ->style('muted');  // Estilo atenuado para filas vacías
```

### empty + selected

```php
// Normalmente no tiene sentido, pero es posible
$emptyRow = $table->createRow('empty_1')
    ->cells(['', '', ''])
    ->empty(true)
    ->selected(false);  // No seleccionable
```

### empty + minRows

```php
$table = UIBuilder::table('data')
    ->minRows(10);

// Agregar filas con datos
for ($i = 0; $i < 3; $i++) {
    $row = $table->createRow("data_$i");
    $row->cells([/* datos */]);
    $table->addRow($row);
}

// Completar con empty rows
for ($i = 3; $i < 10; $i++) {
    $emptyRow = $table->createRow("empty_$i");
    $emptyRow->cells(['', '', ''])
        ->empty(true);
    $table->addRow($emptyRow);
}
```

## JSON Output

### Fila con Datos (Normal)

```json
{
    "type": "tablerow",
    "visible": true,
    "slot": 2,
    "cells": [1, "John Doe", "john@example.com"],
    "selected": false,
    "style": "default",
    "name": "user_1"
    // empty no aparece porque es null
}
```

### Fila Vacía (empty = true)

```json
{
    "type": "tablerow",
    "visible": true,
    "slot": 2,
    "cells": ["", "", ""],
    "selected": false,
    "style": "default",
    "empty": true,
    "name": "empty_row_1"
}
```

## Comportamiento del Cliente

El atributo `empty` es metadata para el cliente. Posibles usos:

### CSS Styling

```css
/* Estilo para filas vacías */
tr[data-empty="true"] {
    opacity: 0.5;
    background-color: #f9f9f9;
    font-style: italic;
}

tr[data-empty="true"] td {
    color: #999;
}
```

### JavaScript

```javascript
// Ocultar filas vacías con un botón
function toggleEmptyRows() {
    const emptyRows = document.querySelectorAll('tr[data-empty="true"]');
    emptyRows.forEach(row => {
        row.style.display = row.style.display === 'none' ? '' : 'none';
    });
}

// Contar filas con datos vs vacías
const allRows = table.rows;
const dataRows = allRows.filter(r => !r.empty);
const emptyRows = allRows.filter(r => r.empty);

console.log(`Data rows: ${dataRows.length}, Empty rows: ${emptyRows.length}`);
```

## Ejemplo Completo: Leaderboard

```php
class LeaderboardService
{
    public function buildLeaderboard(array $topPlayers, int $maxPositions = 10): TableBuilder
    {
        $table = UIBuilder::table('leaderboard')
            ->title('Top Players')
            ->addHeader('Rank', width: '60px', align: TextAlign::CENTER)
            ->addHeader('Player', width: '200px', align: TextAlign::LEFT)
            ->addHeader('Score', width: '100px', align: TextAlign::RIGHT)
            ->addHeader('Wins', width: '80px', align: TextAlign::CENTER)
            ->minRows($maxPositions);

        // Agregar jugadores reales
        foreach ($topPlayers as $index => $player) {
            $rank = $index + 1;
            
            $row = $table->createRow("player_rank_$rank");
            $row->cells([
                $rank,
                $player->name,
                number_format($player->score),
                $player->wins
            ]);
            
            // Estilo especial para top 3
            if ($rank === 1) {
                $row->style('gold');
            } elseif ($rank === 2) {
                $row->style('silver');
            } elseif ($rank === 3) {
                $row->style('bronze');
            }
            
            $table->addRow($row);
        }

        // Rellenar posiciones vacías
        $playerCount = count($topPlayers);
        if ($playerCount < $maxPositions) {
            for ($i = $playerCount + 1; $i <= $maxPositions; $i++) {
                $emptyRow = $table->createRow("empty_rank_$i");
                $emptyRow->cells([$i, '-', '-', '-'])
                    ->empty(true)
                    ->style('muted');
                $table->addRow($emptyRow);
            }
        }

        return $table;
    }
}
```

## Mejores Prácticas

### ✅ DO

```php
// Usar empty para placeholders claros
$emptyRow->cells(['', '', ''])->empty(true);

// Combinar con estilos apropiados
$emptyRow->empty(true)->style('muted');

// Usar con minRows para consistencia
$table->minRows(10);
// ... agregar empty rows para completar
```

### ❌ DON'T

```php
// No marcar filas con datos como empty
$dataRow->cells([1, 'Name', 'Email'])->empty(true);  // ❌ Incorrecto

// No usar empty sin propósito
$row->cells(['', '', ''])->empty(true);  // ❌ Si no hay diferencia visual

// No confundir empty con visible
$row->empty(true);  // Fila vacía pero visible
$row->visible(false);  // Fila oculta (diferente)
```

## Notas Técnicas

1. **Type Safety**: El parámetro es `bool`
2. **Null Filtering**: Cuando es `null`, se filtra automáticamente del JSON
3. **Client-Side**: La lógica de renderizado se implementa en el cliente
4. **Performance**: No afecta el rendimiento del servidor
5. **Semántica**: Es metadata, no controla visibilidad (usar `visible()` para eso)

## Compatibilidad

- ✅ Compatible con todos los métodos de TableRowBuilder
- ✅ Se combina con `style()`, `selected()`, `cells()`
- ✅ Funciona con `minRows` de TableBuilder
- ✅ No rompe código existente (valor por defecto `null`)

## Changelog

**Octubre 19, 2025**
- ✅ Agregado atributo `empty` opcional
- ✅ Método fluido `empty(bool $empty = true)`
- ✅ Filtrado automático de valores null
- ✅ Documentación completa con ejemplos
- ✅ Integración con minRows
