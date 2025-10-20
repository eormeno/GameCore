<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\Align;

echo "=== Test: Simple TableCellBuilder ===\n\n";

try {
    $table = UIBuilder::table()->title('Test');
    $row = $table->createRow('row1');
    
    echo "Row created\n";
    
    $cell = $row->createCell('cell1');
    echo "Cell created\n";
    
    $cell->text('Hello');
    echo "Text set\n";
    
    echo "Calling toJson()...\n";
    $json = $table->toJson();
    
    echo "Success!\n";
    echo json_encode($json, JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
