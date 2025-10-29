# TableBuilder with DataModel Architecture

## Overview

This document describes the enhanced architecture where `TableBuilder` can receive a data model as a parameter and configure itself automatically. This eliminates the need for manual configuration of table dimensions, headers, column widths, pagination, and data filling.

## The Problem We Solved

### Before (Manual Configuration)

```php
// Service had to manually extract and configure everything
$dataModel = $this->getDataModel();
$paginationInfo = $dataModel->getPaginationInfo();
$columns = $dataModel->getColumns();

$table = UIBuilder::table('users_table', $paginationInfo['per_page'], count($columns))
    ->title('Users Table')
    ->pagination(true, $paginationInfo['per_page'])
    ->currentPage($paginationInfo['current_page'])
    ->totalItems($paginationInfo['total_items']);

// Manually configure column widths
$columnIndex = 0;
foreach ($columns as $column) {
    if (isset($column['width'])) {
        $table->columnWidth($columnIndex, $column['width'][0], $column['width'][1]);
    }
    $columnIndex++;
}

// Manually fill headers
$headers = array_column($columns, 'label');
$table->fillHeaderRow($headers);

// Manually fill data with custom method
$this->fillTableWithModelData($table, $dataModel);
```

### After (Automatic Configuration)

```php
// Clean and simple - everything is handled automatically
$dataModel = $this->getDataModel();
$table = UIBuilder::tableWithModel('users_table', $dataModel)
    ->title('Users Table')
    ->align('center')
    ->rowMinHeight(20);
```

## Architecture Components

### 1. UIBuilder Enhancement

**New Method:** `UIBuilder::tableWithModel(?string $name = null, $dataModel = null)`

- Creates a `TableBuilder` instance
- Calls the `dataModel()` method if a model is provided
- Maintains backward compatibility with existing `table()` method

### 2. TableBuilder Enhancement

**New Method:** `TableBuilder::dataModel($dataModel)`

This method expects the data model to implement these methods:

- `getColumns()`: Returns column definitions with labels and widths
- `getPaginationInfo()`: Returns pagination configuration
- `getFormattedPageData()`: Returns formatted data for the current page

**Automatic Configuration Process:**

1. **Dimensions**: Calculates rows (from per_page) and columns (from getColumns count)
2. **Cell Initialization**: Creates all header and data cells
3. **Column Widths**: Applies width constraints from column definitions
4. **Headers**: Fills header row with column labels
5. **Data**: Populates cells with formatted page data
6. **Pagination**: Configures pagination settings

### 3. Data Model Contract

The data model should provide these methods:

```php
interface DataTableModelInterface
{
    public function getColumns(): array;
    public function getPaginationInfo(): array;
    public function getFormattedPageData(): array;
}
```

**Column Definition Format:**
```php
[
    'id' => ['label' => 'Id', 'width' => [50, 80]],
    'name' => ['label' => 'Name', 'width' => [200, 250]],
    'country' => ['label' => 'Country', 'width' => [200, 250]],
    // ...
]
```

**Pagination Info Format:**
```php
[
    'current_page' => 1,
    'per_page' => 5,
    'total_items' => 25,
    // total_pages is calculated automatically
]
```

**Formatted Data Format:**
```php
[
    ['id' => '1', 'name' => 'John', 'country' => 'USA', ...],
    ['id' => '2', 'name' => 'Jane', 'country' => 'Canada', ...],
    // ...
]
```

## Benefits

### 1. **Dramatic Code Reduction**
- From ~30 lines of manual configuration to ~4 lines
- Eliminated boilerplate code in services
- Removed need for custom data filling methods

### 2. **Improved Maintainability**
- Table configuration is centralized in the data model
- Changes to column structure only require model updates
- No duplication of configuration logic

### 3. **Better Separation of Concerns**
- Services focus on business logic, not UI configuration
- Data models handle their own presentation rules
- TableBuilder becomes intelligent and self-configuring

### 4. **Enhanced Consistency**
- All tables using the same model have identical configuration
- Reduces configuration errors and inconsistencies
- Standardizes table behavior across the application

### 5. **Developer Experience**
- Much simpler API for creating data tables
- Less cognitive load when building table UIs
- Self-documenting through the data model

## Usage Examples

### Basic Usage

```php
$dataModel = new UsersDataTableModel(10, 1); // 10 per page, page 1
$table = UIBuilder::tableWithModel('users_table', $dataModel)
    ->title('Users Management')
    ->align('center');
```

### With Traditional API (Still Supported)

```php
$table = UIBuilder::table('manual_table', 5, 3)
    ->title('Manual Table')
    ->pagination(true, 5)
    ->currentPage(1)
    ->totalItems(20);
```

### Null Model Handling

```php
$table = UIBuilder::tableWithModel('empty_table', null); // Gracefully handled
```

## Implementation Details

### Execution Order

The `dataModel()` method follows this sequence:

1. Extract column and pagination configuration
2. Set table dimensions (rows/cols)
3. **Initialize empty cells** ← Critical: Must happen before width application
4. Apply column width constraints
5. Fill header row with labels
6. Populate data cells with formatted data

### Error Handling

- Gracefully handles null data models
- Validates method existence before calling
- Maintains backward compatibility with manual configuration

### Testing

Comprehensive test coverage includes:

- Model-based table creation
- Automatic header filling
- Data population verification
- Column width application
- Null model handling
- Traditional API compatibility

## Future Enhancements

### Planned Features

1. **Sorting Integration**: Data models could provide sort configuration
2. **Filtering Support**: Models could handle filter application
3. **Export Functionality**: Models could provide export data
4. **Advanced Pagination**: Support for cursor-based pagination

### Extensibility

The architecture is designed to be extended:

- Additional model methods can be added
- Custom table builders can inherit the functionality
- New data model types can implement the contract

## Migration Guide

### For Existing Services

1. Replace manual table configuration with `tableWithModel()`
2. Remove custom data filling methods
3. Ensure data models implement required methods
4. Update tests to verify new behavior

### Example Migration

**Before:**
```php
protected function buildBaseUI(): UIContainer
{
    $dataModel = $this->getDataModel();
    $paginationInfo = $dataModel->getPaginationInfo();
    $columns = $dataModel->getColumns();
    
    $table = UIBuilder::table('table', $paginationInfo['per_page'], count($columns))
        ->title('Data Table')
        ->pagination(true, $paginationInfo['per_page'])
        ->currentPage($paginationInfo['current_page'])
        ->totalItems($paginationInfo['total_items']);
        
    // ... many lines of configuration ...
    
    return $container;
}
```

**After:**
```php
protected function buildBaseUI(): UIContainer
{
    $dataModel = $this->getDataModel();
    $table = UIBuilder::tableWithModel('table', $dataModel)
        ->title('Data Table');
        
    return $container;
}
```

## Conclusion

The TableBuilder with DataModel architecture represents a significant improvement in how we handle data tables. It reduces complexity, improves maintainability, and provides a much cleaner developer experience while maintaining full backward compatibility.

The architecture follows the principle of "configuration over convention" where the data model becomes the single source of truth for table behavior, leading to more consistent and maintainable code.