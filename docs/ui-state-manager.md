# UIStateManager - Gestión Centralizada de Cache de UI

## Descripción

`UIStateManager` es una clase estática que centraliza toda la gestión del cache de estado de UI. Proporciona métodos simples y reutilizables para:

- Almacenar y recuperar estado completo de UI
- Actualizar componentes individuales
- Buscar componentes por nombre o tipo
- Obtener/establecer propiedades específicas de componentes

## Ubicación

```
app/Services/UI/Support/UIStateManager.php
```

## Uso Básico

### 1. Almacenar UI Completa

```php
use App\Services\UI\Support\UIStateManager;

// Almacenar el estado completo de UI (generalmente desde AbstractUIService)
$uiArray = $container->toJson();
UIStateManager::store(static::class, $uiArray);

// Con TTL personalizado (default: 1800 segundos = 30 minutos)
UIStateManager::store(static::class, $uiArray, 3600); // 1 hora
```

### 2. Recuperar UI Completa

```php
// Obtener el estado completo de UI
$uiState = UIStateManager::get(static::class);

if ($uiState !== null) {
    // Cache existe
} else {
    // No hay cache, construir UI
}
```

### 3. Buscar Componentes

```php
// Por nombre
$component = UIStateManager::findComponentByName(static::class, 'users_table');

// Por tipo y nombre
$component = UIStateManager::findComponent(
    static::class,
    'table',    // tipo
    'users_table' // nombre
);

// El componente retornado incluye '_id' para referencia
if ($component) {
    $componentId = $component['_id'];
    $currentPage = $component['current_page'];
}
```

### 4. Obtener Propiedad de Componente

```php
// Obtener una propiedad específica
$currentPage = UIStateManager::getComponentProperty(
    static::class,
    'table',          // tipo de componente
    'users_table',    // nombre del componente
    'current_page',   // propiedad
    1                 // valor por defecto
);
```

### 5. Actualizar Propiedad de Componente

```php
// Actualizar una propiedad
$success = UIStateManager::setComponentProperty(
    static::class,
    'table',          // tipo
    'users_table',    // nombre
    'current_page',   // propiedad
    3                 // nuevo valor
);
```

### 6. Actualizar Componente Completo

```php
// Actualizar múltiples propiedades
$componentId = 83563221;
$success = UIStateManager::updateComponent(
    static::class,
    $componentId,
    [
        'current_page' => 3,
        'per_page' => 20,
        'selected_row' => 5
    ]
);
```

### 7. Limpiar Cache

```php
// Eliminar todo el cache de UI para este servicio
UIStateManager::clear(static::class);
```

### 8. Verificar Existencia

```php
// Verificar si existe cache
if (UIStateManager::exists(static::class)) {
    // Cache disponible
}
```

### 9. Obtener Componentes por Tipo

```php
// Obtener todos los componentes de un tipo
$tables = UIStateManager::getComponentsByType(static::class, 'table');

foreach ($tables as $table) {
    echo "Table: {$table['name']}, Page: {$table['current_page']}\n";
}
```

## Ejemplos Prácticos

### Ejemplo 1: Persistir Paginación de Tabla

```php
class TableDemoService extends AbstractUIService
{
    private function getDataModel(): UsersDataTableModel
    {
        // Leer página actual del cache
        $currentPage = UIStateManager::getComponentProperty(
            static::class,
            'table',
            'users_table',
            'current_page',
            1
        );
        
        // Crear modelo con la página correcta
        return new UsersDataTableModel(10, $currentPage);
    }
    
    public function onChangePage(array $params): array
    {
        $page = $params['page'] ?? 1;
        
        // Actualizar la página en el cache
        UIStateManager::setComponentProperty(
            static::class,
            'table',
            'users_table',
            'current_page',
            $page
        );
        
        // Resto de la lógica...
    }
}
```

### Ejemplo 2: Gestionar Estado de Filtros

```php
class DataGridService extends AbstractUIService
{
    public function onApplyFilter(array $params): array
    {
        $filterValue = $params['filter'] ?? '';
        
        // Guardar el filtro activo
        UIStateManager::setComponentProperty(
            static::class,
            'input',
            'search_input',
            'value',
            $filterValue
        );
        
        // Actualizar resultados...
    }
    
    private function getActiveFilter(): string
    {
        // Recuperar el filtro guardado
        return UIStateManager::getComponentProperty(
            static::class,
            'input',
            'search_input',
            'value',
            ''
        );
    }
}
```

### Ejemplo 3: Rastrear Selección de Usuario

```php
class DocumentListService extends AbstractUIService
{
    public function onSelectDocument(array $params): array
    {
        $documentId = $params['document_id'];
        
        // Guardar el documento seleccionado
        UIStateManager::setComponentProperty(
            static::class,
            'table',
            'documents_table',
            'selected_row',
            $documentId
        );
        
        // Actualizar UI...
    }
    
    private function getSelectedDocument(): ?int
    {
        return UIStateManager::getComponentProperty(
            static::class,
            'table',
            'documents_table',
            'selected_row',
            null
        );
    }
}
```

## Integración con AbstractUIService

`UIStateManager` está integrado en `AbstractUIService`, por lo que los siguientes métodos ya lo usan internamente:

```php
// Estos métodos en AbstractUIService usan UIStateManager:
$this->storeUI($container);      // → UIStateManager::store()
$this->getStoredUI();             // → UIStateManager::get()
$this->clearStoredUI();           // → UIStateManager::clear()
```

## Beneficios

1. **Código más limpio**: No necesitas gestionar cache keys manualmente
2. **Reutilizable**: Misma API para todos los servicios
3. **Type-safe**: Métodos específicos para operaciones comunes
4. **Mantenible**: Cambios al sistema de cache se hacen en un solo lugar
5. **Testeable**: Fácil de mockear en pruebas unitarias

## Constantes

```php
UIStateManager::DEFAULT_TTL  // 1800 segundos (30 minutos)
```

## Cache Key Format

Los cache keys se generan automáticamente:
```
ui_state:{ServiceName}:{userId}
```

Ejemplo:
```
ui_state:TableDemoService:zBkFuLRWJhqYxn0th1DRQR61q5358Sm0KLm2gk5F
```

## Notas Importantes

- El cache se asocia automáticamente al usuario actual (Auth o Session)
- Todos los métodos usan `static::class` para obtener el nombre del servicio
- Los componentes retornados siempre incluyen su `_id` para referencia
- El TTL por defecto es 30 minutos, pero puede ser personalizado
- El cache se guarda en el driver configurado en `config/cache.php`
