<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;

echo "=== Test: Auto-fill with new createRow ===\n\n";

$table = UIBuilder::table()
    ->title('Saved Games')
    ->addHeader('#')
    ->addHeader('Game Name')
    ->minRows(5);

// Add 2 data rows using the OLD cells() method
$table->createRow('game_1')->cells([1, 'Game 1']);
$table->createRow('game_2')->cells([2, 'Game 2']);

// Auto-fill should add 3 empty rows
$json = $table->toJson();

echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Count rows
$rowCount = 0;
foreach ($json as $compData) {
    if (isset($compData['type']) && $compData['type'] === 'tablerow') {
        $rowCount++;
    }
}

echo "Total rows: $rowCount (expected 5)\n";
echo ($rowCount === 5 ? "✅ PASS" : "❌ FAIL") . "\n";
