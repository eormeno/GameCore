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
    'description' => 'string',   // Optional: Removal feedback (default: '[REMOVED]')
    'row' => 'int'               // Optional: Row index for UI updates
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

### 1. Custom Field Mapping

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

### 2. Custom Removal Display

Override `getRemovalValuesForRow()` to customize removal appearance:

```php
protected function getRemovalValuesForRow(int $columnCount, string $description): array
{
    $values = [];
    for ($i = 0; $i < $columnCount; $i++) {
        if ($i === 0) {
            $values[$i] = '❌'; // Custom icon for ID column
        } elseif ($i === 1) {
            $values[$i] = $description; // Description in main column
        } else {
            $values[$i] = '---'; // Custom placeholder
        }
    }
    return $values;
}
```

### 3. Data Model Compatibility

The trait works with any data model that provides these methods:

```php
interface DataTableModelInterface
{
    public function updateUser($id, array $data): void;    // Legacy
    public function removeUser($id): void;                 // Legacy
    public function updateRow($id, array $data): void;     // Generic
    public function removeRow($id): void;                  // Generic
    public function getPerPage(): int;
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
// Frontend calls directly with generic parameters
$response = $service->onEditRow([
    'table_name' => 'users_table',
    'id' => 123,
    'data' => ['name' => 'New Name'],
    'row' => 0
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