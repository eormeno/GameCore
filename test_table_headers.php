<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\TextAlign;
use App\Services\UI\Enums\FontWeight;

echo "=== Test TableBuilder Headers - Null Filtering ===\n\n";

// Test 1: Header simple sin parámetros opcionales
echo "Test 1: Header simple\n";
$table1 = UIBuilder::table('simple_table')
    ->addHeader('Name')
    ->addHeader('Email');

$json1 = $table1->toJson();
echo json_encode($json1, JSON_PRETTY_PRINT) . "\n\n";

// Verificar que no hay nulls en headers
$nullCount = 0;
foreach ($json1 as $id => $config) {
    if (isset($config['headers'])) {
        foreach ($config['headers'] as $headerId => $headerData) {
            echo "Verificando header: $headerId\n";
            foreach ($headerData as $key => $value) {
                if ($value === null) {
                    echo "  ❌ ERROR: Found null value for key '$key'\n";
                    $nullCount++;
                } else {
                    echo "  ✅ $key: " . json_encode($value) . "\n";
                }
            }
        }
    }
}

if ($nullCount === 0) {
    echo "\n✅ Test 1 passed: No null values in simple headers\n\n";
} else {
    echo "\n❌ Test 1 failed: Found $nullCount null values\n\n";
}

// Test 2: Header con todos los parámetros
echo "Test 2: Header con todos los parámetros\n";
$table2 = UIBuilder::table('full_table')
    ->addHeader(
        text: 'Full Name',
        id: 'full_name',
        sortable: true,
        align: TextAlign::LEFT,
        width: '200px',
        fontWeight: FontWeight::BOLD,
        color: '#333',
        backgroundColor: '#f0f0f0',
        tooltip: 'User full name',
        sortDirection: 'asc'
    );

$json2 = $table2->toJson();
echo json_encode($json2, JSON_PRETTY_PRINT) . "\n\n";

// Verificar que no hay nulls
$nullCount = 0;
foreach ($json2 as $id => $config) {
    if (isset($config['headers'])) {
        foreach ($config['headers'] as $headerId => $headerData) {
            echo "Verificando header: $headerId\n";
            foreach ($headerData as $key => $value) {
                if ($value === null) {
                    echo "  ❌ ERROR: Found null value for key '$key'\n";
                    $nullCount++;
                } else {
                    echo "  ✅ $key: " . json_encode($value) . "\n";
                }
            }
        }
    }
}

if ($nullCount === 0) {
    echo "\n✅ Test 2 passed: No null values in full headers\n\n";
} else {
    echo "\n❌ Test 2 failed: Found $nullCount null values\n\n";
}

// Test 3: Mix de headers con y sin parámetros opcionales
echo "Test 3: Mix de headers\n";
$table3 = UIBuilder::table('mix_table')
    ->addHeader('ID')
    ->addHeader('Name', sortable: true)
    ->addHeader('Email', width: '250px', align: TextAlign::LEFT)
    ->addHeader('Actions', align: TextAlign::CENTER);

$json3 = $table3->toJson();
echo json_encode($json3, JSON_PRETTY_PRINT) . "\n\n";

// Verificar que no hay nulls
$nullCount = 0;
foreach ($json3 as $id => $config) {
    if (isset($config['headers'])) {
        foreach ($config['headers'] as $headerId => $headerData) {
            foreach ($headerData as $key => $value) {
                if ($value === null) {
                    $nullCount++;
                }
            }
        }
    }
}

if ($nullCount === 0) {
    echo "✅ Test 3 passed: No null values in mixed headers\n\n";
} else {
    echo "❌ Test 3 failed: Found $nullCount null values\n\n";
}

echo "\n=== ALL TESTS COMPLETED ===\n";
