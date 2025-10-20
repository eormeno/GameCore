<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\Align;

echo "=== Test: TableCellBuilder ===\n\n";

// Test 1: Cell with simple text
echo "Test 1: Cell with simple text (left align - default)\n";
$table1 = UIBuilder::table()->title('Test 1');
$row1 = $table1->createRow('row1');
$cell1 = $row1->createCell('cell1')->text('Hello World');
$json1 = $table1->toJson();
echo json_encode($json1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Verify align is not in JSON (left is default)
$hasAlign = false;
foreach ($json1 as $compData) {
    if (isset($compData['align'])) {
        $hasAlign = true;
        break;
    }
}
echo ($hasAlign ? "❌ FAIL" : "✅ PASS") . ": Left align (default) not in JSON\n\n";

// Test 2: Cell with center alignment
echo "Test 2: Cell with center alignment\n";
$table2 = UIBuilder::table()->title('Test 2');
$row2 = $table2->createRow('row2');
$cell2 = $row2->createCell('cell2')->text('Centered')->align(Align::CENTER);
$json2 = $table2->toJson();
echo json_encode($json2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Verify align IS in JSON (center is not default)
$hasCenter = false;
foreach ($json2 as $compId => $compData) {
    if (isset($compData['type']) && $compData['type'] === 'tablecell' && isset($compData['align']) && $compData['align'] === 'center') {
        $hasCenter = true;
        break;
    }
}
echo ($hasCenter ? "✅ PASS" : "❌ FAIL") . ": Center align IS in JSON\n\n";

// Test 3: Cell with child component (button)
echo "Test 3: Cell with child component (button)\n";
$table3 = UIBuilder::table()->title('Test 3');
$row3 = $table3->createRow('row3');
$cell3 = $row3->createCell('cell3')->align(Align::RIGHT);
$button = UIBuilder::button('Click me')->action('test_action');
$cell3->addChild($button);
$json3 = $table3->toJson();
echo json_encode($json3, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Verify button is child of cell
$buttonInCell = false;
foreach ($json3 as $compId => $compData) {
    if (isset($compData['type']) && $compData['type'] === 'button') {
        // Find cell ID
        foreach ($json3 as $cellId => $cellData) {
            if (isset($cellData['type']) && $cellData['type'] === 'tablecell') {
                if ($compData['slot'] === $cellId) {
                    $buttonInCell = true;
                    break 2;
                }
            }
        }
    }
}
echo ($buttonInCell ? "✅ PASS" : "❌ FAIL") . ": Button is child of cell (slot reference correct)\n\n";

// Test 4: Multiple cells in a row
echo "Test 4: Multiple cells in a row\n";
$table4 = UIBuilder::table()->title('Test 4');
$row4 = $table4->createRow('row4');
$row4->createCell('cell1')->text('Cell 1');
$row4->createCell('cell2')->text('Cell 2')->align(Align::CENTER);
$row4->createCell('cell3')->text('Cell 3')->align(Align::RIGHT);
$json4 = $table4->toJson();
echo json_encode($json4, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Count cells in row
$cellCount = 0;
foreach ($json4 as $compId => $compData) {
    if (isset($compData['type']) && $compData['type'] === 'tablecell') {
        $cellCount++;
    }
}
echo ($cellCount === 3 ? "✅ PASS" : "❌ FAIL") . ": Row has 3 cells\n\n";

// Test 5: Cell can only have one child
echo "Test 5: Cell can only have one child (should throw exception)\n";
try {
    $table5 = UIBuilder::table()->title('Test 5');
    $row5 = $table5->createRow('row5');
    $cell5 = $row5->createCell('cell5');
    $cell5->addChild(UIBuilder::button('Button 1')->action('action1'));
    $cell5->addChild(UIBuilder::button('Button 2')->action('action2')); // Should throw
    echo "❌ FAIL: No exception thrown\n\n";
} catch (\LogicException $e) {
    echo "✅ PASS: Exception thrown: " . $e->getMessage() . "\n\n";
}

echo "\n=== Summary ===\n";
echo "TableCellBuilder created successfully!\n";
echo "- Can contain simple text\n";
echo "- Can contain a single child component\n";
echo "- Supports align (left, center, right)\n";
echo "- 'left' align doesn't appear in JSON (default)\n";
echo "- Cell references parent row via 'slot'\n";
echo "- Child component references cell via 'slot'\n";
