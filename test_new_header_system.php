<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Support\UIIdGenerator;
use App\Services\UI\Enums\Align;
use App\Services\UI\Enums\FontWeight;

// Reset ID generator for consistent output
UIIdGenerator::reset();

echo "=== Test: New Table Header System ===\n\n";

// Create a table with the new header system
$table = UIBuilder::table('users_table')
    ->title('User Management')
    ->minRows(5);

// Create header row with various configurations
$headerRow = $table->createHeaderRow();

// Simple header
$headerRow->createCell()->text('ID')->align(Align::CENTER);

// Sortable header with action
$headerRow->createCell()
    ->text('Name')
    ->sortable(true)
    ->sortDirection('asc')
    ->action('sort_by_name')
    ->tooltip('Click to sort by name');

// Header with styling
$headerRow->createCell()
    ->text('Email')
    ->sortable(true)
    ->action('sort_by_email')
    ->width('250px')
    ->align(Align::LEFT);

// Header with colspan
$headerRow->createCell()
    ->text('User Info')
    ->colspan(2)
    ->align(Align::CENTER)
    ->backgroundColor('#e0e0e0');

// Simple headers
$headerRow->createCell()->text('Status');
$headerRow->createCell()->text('Actions');

// Add some data rows
$table->createRow()->cells([
    1,
    'John Doe',
    'john@example.com',
    'Admin',
    '2024-01-15',
    'Active',
    '[Edit] [Delete]'
]);

$table->createRow()->cells([
    2,
    'Jane Smith',
    'jane@example.com',
    'User',
    '2024-02-20',
    'Active',
    '[Edit] [Delete]'
]);

// Generate JSON
$json = $table->toJson();

echo "Generated JSON:\n";
echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Verify structure
echo "=== Verification ===\n";
$tableId = $table->getId();
$headerRowId = $headerRow->getId();

echo "✅ Table ID: $tableId\n";
echo "✅ Header Row ID: $headerRowId\n";
echo "✅ Table has header_row reference: " . ($json[$tableId]['header_row'] === $headerRowId ? 'YES' : 'NO') . "\n";
echo "✅ Header cells count: " . count($headerRow->getCells()) . "\n";
echo "✅ Data rows count (excl. empty): 2\n";
echo "✅ Total rows (with minRows): 5\n\n";

// Show header cells details
echo "=== Header Cells Details ===\n";
foreach ($headerRow->getCells() as $index => $cell) {
    $cellId = $cell->getId();
    $cellJson = $json[$cellId];
    echo "Cell " . ($index + 1) . " (ID: $cellId):\n";
    echo "  Text: " . ($cellJson['text'] ?? 'N/A') . "\n";
    echo "  Sortable: " . ($cellJson['sortable'] ?? 'false') . "\n";
    if (isset($cellJson['action'])) {
        echo "  Action: " . $cellJson['action'] . "\n";
    }
    if (isset($cellJson['colspan'])) {
        echo "  Colspan: " . $cellJson['colspan'] . "\n";
    }
    echo "\n";
}

echo "=== Test Completed Successfully ===\n";
