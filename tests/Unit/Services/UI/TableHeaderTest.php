<?php

use App\Services\UI\UIBuilder;
use App\Services\UI\Support\UIIdGenerator;
use App\Services\UI\Enums\Align;
use App\Services\UI\Enums\FontWeight;

beforeEach(function () {
    UIIdGenerator::reset();
});

test('can create table with header row', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    
    expect($headerRow)->toBeInstanceOf(\App\Services\UI\Components\TableHeaderRowBuilder::class);
    expect($table->getHeaderRow())->toBe($headerRow);
});

test('table can only have one header row', function () {
    $table = UIBuilder::table('test_table');
    $table->createHeaderRow();
    
    expect(fn() => $table->createHeaderRow())
        ->toThrow(\LogicException::class, 'Table already has a header row');
});

test('can create header cells with text', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    
    $cell1 = $headerRow->createCell()->text('Name');
    $cell2 = $headerRow->createCell()->text('Email');
    
    expect($headerRow->getCells())->toHaveCount(2);
    expect($cell1)->toBeInstanceOf(\App\Services\UI\Components\TableHeaderCellBuilder::class);
});

test('header cell can be sortable with action', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    
    $cell = $headerRow->createCell()
        ->text('Name')
        ->sortable(true)
        ->sortDirection('asc')
        ->action('sort_by_name');
    
    $json = $cell->toJson();
    $cellId = $cell->getId();
    
    expect($json[$cellId]['text'])->toBe('Name');
    expect($json[$cellId]['sortable'])->toBe(true);
    expect($json[$cellId]['sort_direction'])->toBe('asc');
    expect($json[$cellId]['action'])->toBe('sort_by_name');
});

test('header cell can set colspan', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    
    $cell = $headerRow->createCell()
        ->text('User Info')
        ->colspan(3);
    
    $json = $cell->toJson();
    $cellId = $cell->getId();
    
    expect($json[$cellId]['text'])->toBe('User Info');
    expect($json[$cellId]['colspan'])->toBe(3);
});

test('header cell colspan must be at least 1', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    $cell = $headerRow->createCell();
    
    expect(fn() => $cell->colspan(0))
        ->toThrow(\InvalidArgumentException::class, 'Colspan must be at least 1');
});

test('header cell can set styling options', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    
    $cell = $headerRow->createCell()
        ->text('Status')
        ->align(Align::CENTER)
        ->width('150px')
        ->color('#333')
        ->backgroundColor('#f0f0f0')
        ->fontWeight(FontWeight::NORMAL)
        ->tooltip('Current status');
    
    $json = $cell->toJson();
    $cellId = $cell->getId();
    
    expect($json[$cellId]['text'])->toBe('Status');
    expect($json[$cellId]['align'])->toBe('center');
    expect($json[$cellId]['width'])->toBe('150px');
    expect($json[$cellId]['color'])->toBe('#333');
    expect($json[$cellId]['background_color'])->toBe('#f0f0f0');
    expect($json[$cellId]['font_weight'])->toBe('normal');
    expect($json[$cellId]['tooltip'])->toBe('Current status');
});

test('sort direction must be asc, desc, or null', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    $cell = $headerRow->createCell();
    
    expect(fn() => $cell->sortDirection('invalid'))
        ->toThrow(\InvalidArgumentException::class, "Sort direction must be 'asc', 'desc', or null");
});

test('table toJson includes header row', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    $headerRow->createCell()->text('Name');
    $headerRow->createCell()->text('Email');
    
    $json = $table->toJson();
    $tableId = $table->getId();
    $headerRowId = $headerRow->getId();
    
    expect($json)->toHaveKey($tableId);
    expect($json)->toHaveKey($headerRowId);
    expect($json[$tableId]['header_row'])->toBe($headerRowId);
});

test('header row has correct parent reference', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    
    $json = $headerRow->toJson();
    $headerRowId = $headerRow->getId();
    
    expect($json[$headerRowId]['parent'])->toBe($table->getId());
});

test('header cells have correct parent reference to header row', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    $cell = $headerRow->createCell()->text('Name');
    
    $json = $cell->toJson();
    $cellId = $cell->getId();
    
    expect($json[$cellId]['parent'])->toBe($headerRow->getId());
});

test('header cell filters default values from JSON', function () {
    $table = UIBuilder::table('test_table');
    $headerRow = $table->createHeaderRow();
    
    $cell = $headerRow->createCell()
        ->text('Name')
        ->sortable(false)  // default value
        ->colspan(1)       // default value
        ->fontWeight(FontWeight::BOLD); // default value
    
    $json = $cell->toJson();
    $cellId = $cell->getId();
    
    expect($json[$cellId])->not->toHaveKey('sortable');
    expect($json[$cellId])->not->toHaveKey('colspan');
    expect($json[$cellId])->not->toHaveKey('font_weight');
    expect($json[$cellId])->not->toHaveKey('visible');
});

test('complete table with header row and data rows', function () {
    $table = UIBuilder::table('users_table')->title('Users');
    
    // Create header
    $headerRow = $table->createHeaderRow();
    $headerRow->createCell()->text('ID')->align(Align::CENTER);
    $headerRow->createCell()->text('Name')->sortable(true)->action('sort_by_name');
    $headerRow->createCell()->text('Email')->sortable(true)->action('sort_by_email');
    
    // Create data rows
    $table->createRow()->cells([1, 'John Doe', 'john@example.com']);
    $table->createRow()->cells([2, 'Jane Smith', 'jane@example.com']);
    
    $json = $table->toJson();
    
    // Verify structure
    expect($json)->toHaveKey($table->getId());
    expect($json)->toHaveKey($headerRow->getId());
    expect($json)->toHaveKey($table->getRowsContainer()->getId());
    
    // Verify header row reference
    expect($json[$table->getId()]['header_row'])->toBe($headerRow->getId());
});
