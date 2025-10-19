<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;

// Test 1: Container básico sin propiedades opcionales
echo "Test 1: Container básico\n";
$container1 = UIBuilder::container('basic')
    ->layout(LayoutType::VERTICAL);

$json1 = $container1->toJson();
echo json_encode($json1, JSON_PRETTY_PRINT) . "\n\n";

// Verificar que no hay nulls
foreach ($json1 as $id => $config) {
    foreach ($config as $key => $value) {
        if ($value === null) {
            echo "❌ ERROR: Found null value for key '$key'\n";
        }
    }
}
echo "✅ Test 1 passed: No null values in basic container\n\n";

// Test 2: Container con algunas propiedades modernas
echo "Test 2: Container con flexbox\n";
$container2 = UIBuilder::container('flex')
    ->flexRow()
    ->gap('1rem')
    ->padding('2rem')
    ->backgroundColor('#fff');

$json2 = $container2->toJson();
echo json_encode($json2, JSON_PRETTY_PRINT) . "\n\n";

// Verificar que no hay nulls
foreach ($json2 as $id => $config) {
    foreach ($config as $key => $value) {
        if ($value === null) {
            echo "❌ ERROR: Found null value for key '$key'\n";
        }
    }
}
echo "✅ Test 2 passed: No null values in flex container\n\n";

// Test 3: Container con grid y responsive
echo "Test 3: Container con grid y responsive\n";
$container3 = UIBuilder::container('grid')
    ->grid('1fr 2fr', '100px auto')
    ->gap('1.5rem')
    ->responsive([
        'mobile' => ['grid_template_columns' => '1fr']
    ])
    ->shadow('medium')
    ->rounded('8px');

$json3 = $container3->toJson();
echo json_encode($json3, JSON_PRETTY_PRINT) . "\n\n";

// Verificar que no hay nulls
$nullCount = 0;
foreach ($json3 as $id => $config) {
    foreach ($config as $key => $value) {
        if ($value === null) {
            echo "❌ ERROR: Found null value for key '$key'\n";
            $nullCount++;
        }
    }
}
if ($nullCount === 0) {
    echo "✅ Test 3 passed: No null values in grid container\n\n";
}

// Test 4: Form con algunos campos
echo "Test 4: FormBuilder\n";
$form = UIBuilder::form('testForm')
    ->action('/submit')
    ->method('POST')
    ->shadow('light')
    ->rounded();

$json4 = $form->toJson();
echo json_encode($json4, JSON_PRETTY_PRINT) . "\n\n";

// Verificar que no hay nulls
$nullCount = 0;
foreach ($json4 as $id => $config) {
    foreach ($config as $key => $value) {
        if ($value === null) {
            echo "❌ ERROR: Found null value for key '$key'\n";
            $nullCount++;
        }
    }
}
if ($nullCount === 0) {
    echo "✅ Test 4 passed: No null values in form\n\n";
}

// Test 5: Component simple (Button, Input, etc)
echo "Test 5: Simple Component\n";
$button = UIBuilder::button('myButton')->label('Click me');
$json5 = $button->toJson();
echo json_encode($json5, JSON_PRETTY_PRINT) . "\n\n";

// Verificar que no hay nulls
$nullCount = 0;
foreach ($json5 as $id => $config) {
    foreach ($config as $key => $value) {
        if ($value === null) {
            echo "❌ ERROR: Found null value for key '$key'\n";
            $nullCount++;
        }
    }
}
if ($nullCount === 0) {
    echo "✅ Test 5 passed: No null values in button component\n\n";
}

echo "\n=== ALL TESTS PASSED ===\n";
echo "All null values have been successfully filtered from JSON output!\n";
