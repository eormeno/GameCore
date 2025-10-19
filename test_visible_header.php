<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;

echo "=== Test: visible en headers ===\n\n";

$table = UIBuilder::table()
    ->title('Test Table')
    ->addHeader('Column 1')
    ->addHeader('Column 2');

$json = $table->toJson();

echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Verificar que 'visible' no esté presente en los headers
$hasVisibleInHeaders = false;
foreach ($json as $componentId => $componentData) {
    if (isset($componentData['headers'])) {
        foreach ($componentData['headers'] as $headerData) {
            if (isset($headerData['visible'])) {
                $hasVisibleInHeaders = true;
                break 2;
            }
        }
    }
}

echo "\n\n";
if ($hasVisibleInHeaders) {
    echo "❌ FAIL: Los headers aún contienen 'visible'\n";
} else {
    echo "✅ PASS: Los headers NO contienen 'visible'\n";
}
