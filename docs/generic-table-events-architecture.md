# Generic Table Events Architecture with DataTableEventsTrait

## Overview

This document describes the implementation of generic table event handling through the `DataTableEventsTrait`. This trait provides standardized methods for handling row editing and removal across multiple tables, eliminating the need for repetitive event handling code.

## The Problem We Solved

### Before (Specific Event Handlers)

Each service needed to implement specific event handlers for each table:

```php
public function onEditUser(array $params): array
{
    $userId = $params['user_id'] ?? null;
    $row = $params['row'] ?? null;
    $userName = $params['name'] ?? 'Unknown';
    
    // 30+ lines of manual UI updates...
}

public function onRemoveUser(array $params): array
{
    $userId = $params['user_id'] ?? null;
    $row = $params['row'] ?? null;
    
    // 40+ lines of manual cell updates...
}

// If you had products table, you'd need:
public function onEditProduct(array $params): array { /* duplicate logic */ }
public function onRemoveProduct(array $params): array { /* duplicate logic */ }
```

### After (Generic Event Handlers)

One trait handles all tables with consistent API:

```php
// Edit any row in any table
$this->onEditRow([
    'table_name' => 'users_table',
    'id' => 123,
    'data' => ['name' => 'New Name'],
    'row' => 0
]);

// Remove any row from any table
$this->onRemoveRow([
    'table_name' => 'products_table',
    'id' => 456,
    'description' => '[PRODUCT DELETED]',
    'row' => 2
]);
```

## Architecture Components

### 1. DataTableEventsTrait

**Location:** `app/Traits/DataTableEventsTrait.php`

**Core Methods:**
- `onEditRow(array $params): array` - Generic row editing
- `onRemoveRow(array $params): array` - Generic row removal  
- `onChangeTablePage(array $params): array` - Generic page navigation

**Required Implementation:**
- `getDataModelForTable(string $tableName)` - Must be implemented by using class

**Parameter Structure:**

**Edit Row Parameters:**
```php
[
    'table_name' => 'string',    // Required: Table identifier
    'id' => 'mixed',             // Required: Row identifier  
    'data' => 'array',           // Required: New data to update
    'row' => 'int'               // Optional: Row index for UI updates
]
```

**Remove Row Parameters:**
```php
[
    'table_name' => 'string',    // Required: Table identifier
    'id' => 'mixed',             // Required: Row identifier to remove
    'description' => 'string',   // Optional: Removal feedback (overridden by model config)
    'row' => 'int'               // Optional: Row index for UI updates
]
```

**Change Page Parameters:**
```php
[
    'table_name' => 'string',    // Required: Table identifier
    'page' => 'int'              // Required: Target page number (1-based)
]
```

### 2. Multi-Table Support

The `table_name` parameter enables handling multiple tables in the same service:

```php
protected function getDataModelForTable(string $tableName)
{
    switch ($tableName) {
        case 'users_table':
            return $this->getUsersModel();
            
        case 'products_table':
            return $this->getProductsModel();
            
        case 'orders_table':
            return $this->getOrdersModel();
            
        default:
            return null;
    }
}
```

### 3. Backward Compatibility

Existing methods can be refactored to use the trait while maintaining their original API:

```php
public function onEditUser(array $params): array
{
    // Map legacy parameters to generic format
    return $this->onEditRow([
        'table_name' => 'users_table',
        'id' => $params['user_id'] ?? null,
        'row' => $params['row'] ?? null,
        'data' => [
            'name' => ($params['name'] ?? 'Unknown') . ' [EDITED]'
        ]
    ]);
}
```

## Implementation Examples

### Single Table Service

```php
class TableDemoService extends AbstractUIService
{
    use DataTableEventsTrait;
    
    protected function getDataModelForTable(string $tableName)
    {
        if ($tableName === 'users_table') {
            return $this->getDataModel();
        }
        return null;
    }
    
    // Legacy compatibility maintained
    public function onEditUser(array $params): array
    {
        return $this->onEditRow([
            'table_name' => 'users_table',
            'id' => $params['user_id'] ?? null,
            'row' => $params['row'] ?? null,
            'data' => ['name' => $params['name'] . ' [EDITED]']
        ]);
    }
}
```

### Multi-Table Service

```php
class MultiTableDemoService extends AbstractUIService
{
    use DataTableEventsTrait;
    
    protected function getDataModelForTable(string $tableName)
    {
        switch ($tableName) {
            case 'users_table':
                return $this->getUsersModel();
            case 'products_table':
                return $this->getProductsModel();
            default:
                return null;
        }
    }
    
    // Generic methods work for any table
    public function editAnyRow(array $params): array
    {
        return $this->onEditRow($params);
    }
    
    public function removeAnyRow(array $params): array
    {
        return $this->onRemoveRow($params);
    }
}
```

## Advanced Features

### 1. Configurable Removal Display

Data models can now provide their own removal configuration through `getRemovedRowConfig()`:

```php
public function getRemovedRowConfig(): array
{
    return [
        'primary_message' => '[USER REMOVED]',  // Custom message
        'secondary_message' => '---',           // Secondary placeholder
        'id_placeholder' => '❌',               // Visual indicator for ID
        'button_placeholder' => '⛔',           // Visual indicator for buttons
        'empty_placeholder' => '',              // Empty cells
    ];
}
```

The model also provides `getRemovalValues(int $columnCount)` to generate removal values for all columns automatically.

### 2. Generic Pagination

The trait now handles page navigation generically:

```php
// Navigate to page 3 of any table
$this->onChangeTablePage([
    'table_name' => 'users_table',
    'page' => 3
]);

// Works with any table
$this->onChangeTablePage([
    'table_name' => 'products_table', 
    'page' => 1
]);
```

### 3. Custom Field Mapping

Override `getCellNameForData()` to map data fields to cell positions:

```php
protected function getCellNameForData(int $pageRow, string $dataKey, $dataModel): ?string
{
    $fieldMapping = [
        'name' => 1,        // Column 1 for user name
        'title' => 1,       // Column 1 for product title
        'country' => 2,     // Column 2 for user country
        'category' => 2,    // Column 2 for product category
        'price' => 3,       // Column 3 for product price
    ];

    $columnIndex = $fieldMapping[$dataKey] ?? null;
    return $columnIndex !== null ? "{$pageRow}_{$columnIndex}" : null;
}
```

### 4. Custom Column Mapping

Override `getColumnMappingForModel()` to map data fields to table columns:

```php
protected function getColumnMappingForModel($dataModel): array
{
    return [
        'id' => 0,          // ID in column 0
        'name' => 1,        // Name in column 1  
        'title' => 1,       // Product title also in column 1
        'country' => 2,     // Country in column 2
        'category' => 2,    // Product category in column 2
        'actions' => 3,     // Action buttons in column 3
        'remove' => 4,      // Remove button in column 4
    ];
}
```

### 5. Data Model Compatibility

The trait works with any data model that provides these methods:

```php
interface DataTableModelInterface
{
    // Basic operations
    public function updateUser($id, array $data): void;    // Legacy
    public function removeUser($id): void;                 // Legacy
    public function updateRow($id, array $data): void;     // Generic
    public function removeRow($id): void;                  // Generic
    
    // Pagination support
    public function getPerPage(): int;
    public function setCurrentPage(int $page): void;
    public function getFormattedPageData(): array;
    
    // Configurable removal (optional)
    public function getRemovedRowConfig(): array;
    public function getRemovalValues(int $columnCount): array;
}
```

## Benefits

### 1. **Code Reusability**
- One trait handles all table events
- No duplication of event handling logic
- Consistent behavior across all tables

### 2. **Multi-Table Support**
- Single service can manage multiple tables
- `table_name` parameter distinguishes between tables
- Scalable architecture for complex pages

### 3. **Maintainability**
- Centralized event handling logic
- Easy to add new event types
- Consistent API across all services

### 4. **Flexibility**
- Override methods for custom behavior
- Support for different data models
- Configurable field mapping and removal display

### 5. **Backward Compatibility**
- Existing methods continue to work
- Gradual migration path
- No breaking changes

## Usage Patterns

### Pattern 1: Direct Generic Usage

```php
// Edit any row in any table
$service->onEditRow([
    'table_name' => 'users_table',
    'id' => 123,
    'data' => ['name' => 'New Name'],
    'row' => 0
]);

// Remove any row from any table
$service->onRemoveRow([
    'table_name' => 'products_table',
    'id' => 456,
    'row' => 2
]);

// Navigate any table to any page
$service->onChangeTablePage([
    'table_name' => 'orders_table',
    'page' => 3
]);
```

### Pattern 2: Wrapper Methods

```php
// Service provides semantic wrapper methods
public function editUser(array $params): array
{
    return $this->onEditRow([
        'table_name' => 'users_table',
        'id' => $params['user_id'],
        'data' => $params['data'],
        'row' => $params['row']
    ]);
}
```

### Pattern 3: Legacy Compatibility

```php
// Existing frontend code continues to work
public function onEditUser(array $params): array
{
    return $this->onEditRow([
        'table_name' => 'users_table',
        'id' => $params['user_id'],
        'data' => ['name' => $params['name'] . ' [EDITED]'],
        'row' => $params['row']
    ]);
}

public function onChangePage(array $params): array
{
    return $this->onChangeTablePage([
        'table_name' => 'users_table',
        'page' => $params['page'] ?? 1
    ]);
}
```

## Error Handling

The trait provides robust error handling:

```php
// Missing required parameters
$this->onEditRow(['id' => 123]); // Returns []

// Invalid table name
$this->onEditRow(['table_name' => 'invalid', 'id' => 123]); // Returns []

// No data model found
// getDataModelForTable() returns null → Returns []
```

## Testing

Comprehensive test coverage includes:

- Generic edit/remove functionality
- Multi-table parameter handling
- Invalid parameter handling
- Backward compatibility verification
- Custom description support
- Data model integration

## Migration Guide

### Step 1: Add the Trait

```php
use App\Traits\DataTableEventsTrait;

class YourService extends AbstractUIService
{
    use DataTableEventsTrait;
    
    // ...
}
```

### Step 2: Implement Required Method

```php
protected function getDataModelForTable(string $tableName)
{
    switch ($tableName) {
        case 'your_table':
            return $this->getYourDataModel();
        default:
            return null;
    }
}
```

### Step 3: Refactor Existing Methods (Optional)

```php
public function onEditYourEntity(array $params): array
{
    return $this->onEditRow([
        'table_name' => 'your_table',
        'id' => $params['entity_id'],
        'data' => $params['data'],
        'row' => $params['row']
    ]);
}
```

## Conclusion

The `DataTableEventsTrait` represents a significant architectural improvement that:

- **Eliminates code duplication** in table event handling
- **Enables multi-table support** through consistent API
- **Maintains backward compatibility** with existing code
- **Provides flexibility** for customization
- **Simplifies testing** through consistent behavior

This trait transforms table event handling from repetitive, error-prone code into a clean, maintainable, and scalable architecture that can grow with your application's needs.