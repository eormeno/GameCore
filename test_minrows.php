<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\TextAlign;

echo "=== Test TableBuilder minRows ===\n\n";

// Test 1: Tabla sin minRows (comportamiento predeterminado)
echo "Test 1: Tabla sin minRows\n";
$table1 = UIBuilder::table('table_no_min')
    ->title('Users')
    ->addHeader('ID')
    ->addHeader('Name')
    ->addHeader('Email');

$json1 = $table1->toJson();
echo json_encode($json1, JSON_PRETTY_PRINT) . "\n\n";

if (!isset($json1[array_key_first($json1)]['min_rows'])) {
    echo "✅ Test 1 passed: min_rows is null (filtered out)\n\n";
} else {
    echo "❌ Test 1 failed: min_rows should be filtered out\n\n";
}

// Test 2: Tabla con minRows = 5
echo "Test 2: Tabla con minRows = 5\n";
$table2 = UIBuilder::table('table_with_min')
    ->title('Products')
    ->addHeader('ID')
    ->addHeader('Name', width: '200px', align: TextAlign::LEFT)
    ->addHeader('Price', align: TextAlign::RIGHT)
    ->addHeader('Stock', align: TextAlign::CENTER)
    ->minRows(5);

$json2 = $table2->toJson();
echo json_encode($json2, JSON_PRETTY_PRINT) . "\n\n";

$tableId = array_key_first($json2);
if (isset($json2[$tableId]['min_rows']) && $json2[$tableId]['min_rows'] === 5) {
    echo "✅ Test 2 passed: min_rows is set to 5\n";
} else {
    echo "❌ Test 2 failed: min_rows should be 5\n";
}

echo "\nVerificando estructura completa:\n";
echo "  - title: " . $json2[$tableId]['title'] . "\n";
echo "  - min_rows: " . $json2[$tableId]['min_rows'] . "\n";
echo "  - pagination: " . ($json2[$tableId]['pagination'] ? 'true' : 'false') . "\n";
echo "  - headers count: " . count($json2[$tableId]['headers']) . "\n\n";

// Test 3: Tabla con minRows = 10 y pagination
echo "Test 3: Tabla con minRows = 10 y pagination\n";
$table3 = UIBuilder::table('table_paginated')
    ->title('Orders')
    ->addHeader('Order #')
    ->addHeader('Customer')
    ->addHeader('Total')
    ->addHeader('Status')
    ->minRows(10)
    ->pagination(true);

$json3 = $table3->toJson();
echo json_encode($json3, JSON_PRETTY_PRINT) . "\n\n";

$tableId = array_key_first($json3);
if (isset($json3[$tableId]['min_rows']) && 
    $json3[$tableId]['min_rows'] === 10 && 
    $json3[$tableId]['pagination'] === true) {
    echo "✅ Test 3 passed: min_rows = 10 and pagination = true\n\n";
} else {
    echo "❌ Test 3 failed\n\n";
}

// Test 4: Method chaining
echo "Test 4: Method chaining completo\n";
$table4 = UIBuilder::table('complete_table')
    ->title('Employees')
    ->addHeader('ID', width: '50px')
    ->addHeader('Name', width: '200px', align: TextAlign::LEFT)
    ->addHeader('Position', width: '150px')
    ->addHeader('Department', width: '150px')
    ->addHeader('Salary', width: '100px', align: TextAlign::RIGHT)
    ->minRows(15)
    ->pagination(true);

$json4 = $table4->toJson();

$tableId = array_key_first($json4);
echo "Configuración final:\n";
echo "  - title: " . $json4[$tableId]['title'] . "\n";
echo "  - min_rows: " . $json4[$tableId]['min_rows'] . "\n";
echo "  - pagination: " . ($json4[$tableId]['pagination'] ? 'true' : 'false') . "\n";
echo "  - headers: " . count($json4[$tableId]['headers']) . "\n";

if ($json4[$tableId]['min_rows'] === 15 && 
    $json4[$tableId]['pagination'] === true &&
    count($json4[$tableId]['headers']) === 5) {
    echo "\n✅ Test 4 passed: All configurations applied correctly\n\n";
} else {
    echo "\n❌ Test 4 failed\n\n";
}

echo "=== ALL TESTS COMPLETED ===\n";
echo "\nUso de minRows:\n";
echo "- Si la tabla tiene menos filas que minRows, se rellenan con filas vacías\n";
echo "- Útil para mantener un tamaño consistente de tabla en la UI\n";
echo "- Se combina perfectamente con pagination\n";
echo "- null por defecto (no se incluye en JSON)\n";
