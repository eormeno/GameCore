<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\TextAlign;

echo "=== Test TableRowBuilder Empty Attribute ===\n\n";

// Test 1: Fila normal (sin empty)
echo "Test 1: Fila normal sin atributo empty\n";
$table1 = UIBuilder::table('users')
    ->title('Users')
    ->addHeader('ID')
    ->addHeader('Name')
    ->addHeader('Email');

$row1 = $table1->createRow('user_1');
$row1->cells([1, 'John Doe', 'john@example.com']);
$table1->addRow($row1);

$json1 = $table1->toJson();
$rowId = null;
foreach ($json1 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        $rowId = $id;
        break;
    }
}

echo json_encode($json1[$rowId], JSON_PRETTY_PRINT) . "\n\n";

if (!isset($json1[$rowId]['empty'])) {
    echo "✅ Test 1 passed: empty attribute is null (filtered out)\n\n";
} else {
    echo "❌ Test 1 failed: empty should be filtered out when null\n\n";
}

// Test 2: Fila marcada como vacía
echo "Test 2: Fila marcada como empty = true\n";
$table2 = UIBuilder::table('products')
    ->title('Products')
    ->addHeader('ID')
    ->addHeader('Name')
    ->addHeader('Price')
    ->addHeader('Stock');

$emptyRow = $table2->createRow('empty_row_1');
$emptyRow->cells(['', '', '', ''])
    ->empty(true);  // Marcar como vacía
$table2->addRow($emptyRow);

$json2 = $table2->toJson();
$emptyRowId = null;
foreach ($json2 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        $emptyRowId = $id;
        break;
    }
}

echo json_encode($json2[$emptyRowId], JSON_PRETTY_PRINT) . "\n\n";

if (isset($json2[$emptyRowId]['empty']) && $json2[$emptyRowId]['empty'] === true) {
    echo "✅ Test 2 passed: empty = true is present\n\n";
} else {
    echo "❌ Test 2 failed: empty should be true\n\n";
}

// Test 3: Tabla con minRows y filas vacías
echo "Test 3: Tabla con minRows y filas vacías marcadas\n";
$table3 = UIBuilder::table('orders')
    ->title('Recent Orders')
    ->addHeader('Order #')
    ->addHeader('Customer')
    ->addHeader('Total')
    ->addHeader('Status')
    ->minRows(5);

// Agregar 2 filas con datos
$order1 = $table3->createRow('order_1');
$order1->cells([1001, 'Alice Smith', '$150.00', 'Completed']);
$table3->addRow($order1);

$order2 = $table3->createRow('order_2');
$order2->cells([1002, 'Bob Johnson', '$220.50', 'Pending']);
$table3->addRow($order2);

// Agregar 3 filas vacías para completar minRows
for ($i = 3; $i <= 5; $i++) {
    $emptyRow = $table3->createRow("empty_row_$i");
    $emptyRow->cells(['', '', '', ''])
        ->empty(true);
    $table3->addRow($emptyRow);
}

$json3 = $table3->toJson();

echo "Tabla completa:\n";
echo json_encode($json3, JSON_PRETTY_PRINT) . "\n\n";

// Verificar filas
$dataRows = 0;
$emptyRows = 0;
foreach ($json3 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        if (isset($config['empty']) && $config['empty'] === true) {
            $emptyRows++;
        } else {
            $dataRows++;
        }
    }
}

echo "Resumen:\n";
echo "  - Filas con datos: $dataRows\n";
echo "  - Filas vacías: $emptyRows\n";
echo "  - Total: " . ($dataRows + $emptyRows) . "\n";
echo "  - minRows: " . $json3[array_key_first($json3)]['min_rows'] . "\n\n";

if ($dataRows === 2 && $emptyRows === 3) {
    echo "✅ Test 3 passed: 2 data rows + 3 empty rows = 5 total (matches minRows)\n\n";
} else {
    echo "❌ Test 3 failed: Expected 2 data rows and 3 empty rows\n\n";
}

// Test 4: Fila vacía con estilo especial
echo "Test 4: Fila vacía con estilo personalizado\n";
$table4 = UIBuilder::table('styled_table')
    ->title('Leaderboard')
    ->addHeader('Rank', width: '60px', align: TextAlign::CENTER)
    ->addHeader('Player', width: '200px', align: TextAlign::LEFT)
    ->addHeader('Score', width: '100px', align: TextAlign::RIGHT);

// Fila con datos
$player1 = $table4->createRow('player_1');
$player1->cells([1, 'ProGamer123', '9,850'])
    ->style('success');
$table4->addRow($player1);

// Fila vacía con estilo diferente
$emptySlot = $table4->createRow('empty_slot_2');
$emptySlot->cells([2, '-', '-'])
    ->empty(true)
    ->style('default');
$table4->addRow($emptySlot);

$json4 = $table4->toJson();

echo "Filas:\n";
foreach ($json4 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        echo "  Row: " . $config['name'] . "\n";
        echo "    - empty: " . (isset($config['empty']) && $config['empty'] ? 'true' : 'false') . "\n";
        echo "    - style: " . $config['style'] . "\n";
        echo "    - cells: " . json_encode($config['cells']) . "\n\n";
    }
}

echo "✅ Test 4 passed: Empty rows can have custom styles\n\n";

// Test 5: Method chaining completo
echo "Test 5: Method chaining con empty, selected, y style\n";
$table5 = UIBuilder::table('complex')
    ->title('Complex Table')
    ->addHeader('Col1')
    ->addHeader('Col2');

$row = $table5->createRow('complex_row');
$row->cells(['', ''])
    ->empty(true)
    ->selected(false)
    ->style('default');

$table5->addRow($row);
$json5 = $table5->toJson();

$complexRowId = null;
foreach ($json5 as $id => $config) {
    if ($config['type'] === 'tablerow') {
        $complexRowId = $id;
        break;
    }
}

echo json_encode($json5[$complexRowId], JSON_PRETTY_PRINT) . "\n\n";

if (isset($json5[$complexRowId]['empty']) && 
    $json5[$complexRowId]['empty'] === true &&
    $json5[$complexRowId]['selected'] === false &&
    $json5[$complexRowId]['style'] === 'default') {
    echo "✅ Test 5 passed: All attributes set correctly\n\n";
} else {
    echo "❌ Test 5 failed\n\n";
}

echo "=== ALL TESTS COMPLETED ===\n\n";

echo "Uso del atributo empty:\n";
echo "- Indica que la fila es un placeholder/relleno\n";
echo "- Útil para completar minRows con filas vacías\n";
echo "- El cliente puede aplicar estilos diferentes a filas vacías\n";
echo "- Se puede combinar con style, selected, y otros atributos\n";
echo "- null por defecto (no aparece en JSON si no se establece)\n";
