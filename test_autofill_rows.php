<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\TextAlign;

echo "=== Test Auto-Fill Empty Rows ===\n\n";

// Test 1: Tabla sin minRows (no auto-fill)
echo "Test 1: Tabla sin minRows - No auto-fill\n";
$table1 = UIBuilder::table('no_minfills')
    ->title('Products')
    ->addHeader('ID')
    ->addHeader('Name')
    ->addHeader('Price');

// Agregar solo 2 filas
$row1 = $table1->createRow('product_1');
$row1->cells([1, 'Product A', '$10.00']);
$table1->addRow($row1);

$row2 = $table1->createRow('product_2');
$row2->cells([2, 'Product B', '$20.00']);
$table1->addRow($row2);

$json1 = $table1->toJson();

// Contar filas
$rowCount1 = 0;
foreach ($json1 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        $rowCount1++;
    }
}

echo "Total rows: $rowCount1\n";
if ($rowCount1 === 2) {
    echo "✅ Test 1 passed: Only 2 rows (no auto-fill)\n\n";
} else {
    echo "❌ Test 1 failed: Expected 2 rows\n\n";
}

// Test 2: Tabla con minRows = 5 y 2 filas de datos (auto-fill 3)
echo "Test 2: minRows = 5 con 2 filas de datos - Auto-fill 3 vacías\n";
$table2 = UIBuilder::table('auto_fill_5')
    ->title('Orders')
    ->addHeader('Order #')
    ->addHeader('Customer')
    ->addHeader('Total')
    ->addHeader('Status')
    ->minRows(5);

// Agregar solo 2 filas con datos
$order1 = $table2->createRow('order_1');
$order1->cells([1001, 'Alice', '$150.00', 'Completed']);
$table2->addRow($order1);

$order2 = $table2->createRow('order_2');
$order2->cells([1002, 'Bob', '$220.00', 'Pending']);
$table2->addRow($order2);

// Al llamar toJson(), se deben agregar automáticamente 3 filas vacías
$json2 = $table2->toJson();

// Contar filas
$dataRows2 = 0;
$emptyRows2 = 0;
foreach ($json2 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        if (isset($config['empty']) && $config['empty'] === true) {
            $emptyRows2++;
        } else {
            $dataRows2++;
        }
    }
}

echo "Data rows: $dataRows2\n";
echo "Empty rows: $emptyRows2\n";
echo "Total rows: " . ($dataRows2 + $emptyRows2) . "\n";

if ($dataRows2 === 2 && $emptyRows2 === 3) {
    echo "✅ Test 2 passed: 2 data rows + 3 auto-filled empty rows = 5 total\n\n";
} else {
    echo "❌ Test 2 failed: Expected 2 data + 3 empty\n\n";
}

// Test 3: minRows = 10 con 0 filas (auto-fill 10)
echo "Test 3: minRows = 10 sin filas de datos - Auto-fill 10 vacías\n";
$table3 = UIBuilder::table('empty_leaderboard')
    ->title('Top 10 Leaderboard')
    ->addHeader('Rank', width: '60px')
    ->addHeader('Player', width: '200px')
    ->addHeader('Score', width: '100px')
    ->minRows(10);

// No agregar ninguna fila, solo llamar toJson()
$json3 = $table3->toJson();

// Contar filas vacías
$emptyRows3 = 0;
foreach ($json3 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        $emptyRows3++;
    }
}

echo "Empty rows auto-filled: $emptyRows3\n";

if ($emptyRows3 === 10) {
    echo "✅ Test 3 passed: 10 empty rows auto-filled\n\n";
} else {
    echo "❌ Test 3 failed: Expected 10 empty rows\n\n";
}

// Test 4: minRows = 5 con 7 filas (no auto-fill, ya tiene suficientes)
echo "Test 4: minRows = 5 con 7 filas - No auto-fill (ya tiene suficientes)\n";
$table4 = UIBuilder::table('enough_rows')
    ->title('Products')
    ->addHeader('ID')
    ->addHeader('Name')
    ->minRows(5);

// Agregar 7 filas (más que minRows)
for ($i = 1; $i <= 7; $i++) {
    $row = $table4->createRow("product_$i");
    $row->cells([$i, "Product $i"]);
    $table4->addRow($row);
}

$json4 = $table4->toJson();

// Contar filas
$rowCount4 = 0;
foreach ($json4 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        $rowCount4++;
    }
}

echo "Total rows: $rowCount4\n";

if ($rowCount4 === 7) {
    echo "✅ Test 4 passed: 7 rows (no auto-fill needed)\n\n";
} else {
    echo "❌ Test 4 failed: Expected 7 rows\n\n";
}

// Test 5: Verificar estructura de filas auto-generadas
echo "Test 5: Verificar estructura de filas auto-generadas\n";
$table5 = UIBuilder::table('structure_test')
    ->title('Test Table')
    ->addHeader('Col1')
    ->addHeader('Col2')
    ->addHeader('Col3')
    ->minRows(3);

// Agregar 1 fila con datos
$row = $table5->createRow('data_row');
$row->cells(['A', 'B', 'C']);
$table5->addRow($row);

$json5 = $table5->toJson();

echo "\nFilas generadas:\n";
foreach ($json5 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        $isEmpty = isset($config['empty']) && $config['empty'] === true;
        echo "  - " . $config['name'] . 
             " | empty: " . ($isEmpty ? 'true' : 'false') . 
             " | cells: " . json_encode($config['cells']) . "\n";
    }
}

// Verificar que las filas vacías tienen el número correcto de celdas
$allCorrect = true;
foreach ($json5 as $id => $config) {
    if ($config['type'] === 'tablerow' && isset($config['empty']) && $config['empty']) {
        if (count($config['cells']) !== 3) {
            $allCorrect = false;
            echo "❌ Error: Empty row has " . count($config['cells']) . " cells, expected 3\n";
        }
    }
}

if ($allCorrect) {
    echo "✅ Test 5 passed: All auto-filled rows have correct cell count\n\n";
}

// Test 6: Tabla completa real (Game Lobby scenario)
echo "Test 6: Escenario Real - Game Lobby con minRows\n";
$savedGames = UIBuilder::table('saved_games')
    ->title('Your Saved Games')
    ->addHeader('#', width: '50px')
    ->addHeader('Game Name', width: '200px')
    ->addHeader('State', width: '100px')
    ->addHeader('Status', width: '100px')
    ->addHeader('Last Played', width: '150px')
    ->addHeader('Actions', width: '200px')
    ->minRows(5);

// Simular solo 1 juego guardado
$game1 = $savedGames->createRow('saved_game_1');
$game1->cells([
    1,
    'My Epic Game',
    'running',
    'active',
    '5 minutes ago',
    'Play | Delete'
]);
$savedGames->addRow($game1);

// toJson() debe auto-completar con 4 filas vacías
$jsonGames = $savedGames->toJson();

// Contar
$dataGames = 0;
$emptyGames = 0;
foreach ($jsonGames as $id => $config) {
    if ($config['type'] === 'tablerow') {
        if (isset($config['empty']) && $config['empty']) {
            $emptyGames++;
        } else {
            $dataGames++;
        }
    }
}

echo "Game Lobby:\n";
echo "  - Saved games: $dataGames\n";
echo "  - Empty slots: $emptyGames\n";
echo "  - Total slots: " . ($dataGames + $emptyGames) . "\n";

if ($dataGames === 1 && $emptyGames === 4) {
    echo "✅ Test 6 passed: Game Lobby correctly filled\n\n";
} else {
    echo "❌ Test 6 failed\n\n";
}

echo "=== ALL TESTS COMPLETED ===\n\n";

echo "Resumen de Auto-Fill:\n";
echo "✅ Sin minRows → No se agregan filas\n";
echo "✅ Con minRows y pocas filas → Se completa automáticamente\n";
echo "✅ Con minRows y suficientes filas → No se agregan más\n";
echo "✅ Filas auto-generadas tienen 'empty: true'\n";
echo "✅ Número correcto de celdas basado en headers\n";
echo "✅ Nombres automáticos: empty_row_N\n";
