# Actualización de Caché de Componentes

## Problema

Cuando modificas un atributo de un componente fuera del contexto de un evento (por ejemplo, desde un comando, job, o proceso en background), esos cambios solo afectan al objeto en memoria pero NO se persisten automáticamente en el caché.

## Solución

Usa el método `updateComponentCache()` para modificar propiedades de componentes y actualizar el caché inmediatamente.

## API

### `updateComponentCache(string|int $identifier, array $properties): void`

**Parámetros:**
- `$identifier`: ID numérico del componente O nombre del componente (string)
- `$properties`: Array asociativo de propiedades a actualizar `['key' => 'value']`

**Lanza:** `RuntimeException` si el componente no existe en caché

## Ejemplos de Uso

### 1. Actualizar por ID del Componente

```php
// Desde un comando Artisan
class UpdateTableCommand extends Command
{
    public function handle()
    {
        $service = new TableDemoService();
        
        // Actualizar texto de una celda específica
        $service->updateComponentCache(83561270, [
            'text' => 'Updated from Command'
        ]);
        
        $this->info('Cell updated in cache!');
    }
}
```

### 2. Actualizar por Nombre del Componente

```php
// Desde un Job
class ProcessDataJob implements ShouldQueue
{
    public function handle()
    {
        $service = new MyUIService();
        
        // Actualizar un label por su nombre
        $service->updateComponentCache('status_label', [
            'text' => 'Processing...',
            'style' => 'warning'
        ]);
        
        // ... procesar datos ...
        
        $service->updateComponentCache('status_label', [
            'text' => 'Complete!',
            'style' => 'success'
        ]);
    }
}
```

### 3. Actualizar Múltiples Propiedades

```php
// Actualizar varios atributos a la vez
$service->updateComponentCache('my_button', [
    'label' => 'Click Me!',
    'style' => 'primary',
    'enabled' => true,
    'visible' => true
]);
```

### 4. Actualizar desde un Evento de Laravel

```php
class UserCreatedListener
{
    public function handle(UserCreated $event)
    {
        $service = new DashboardService();
        
        // Actualizar contador de usuarios
        $service->updateComponentCache('user_count_label', [
            'text' => "Total Users: {$event->totalUsers}"
        ]);
    }
}
```

### 5. Actualizar desde un Controller (fuera de evento UI)

```php
class AdminController extends Controller
{
    public function updateStatus(Request $request)
    {
        // Procesar lógica de negocio
        $status = $this->processStatusUpdate();
        
        // Actualizar UI en caché para todos los usuarios
        $service = new StatusService();
        $service->updateComponentCache('system_status', [
            'text' => $status,
            'style' => $status === 'Online' ? 'success' : 'danger'
        ]);
        
        return response()->json(['success' => true]);
    }
}
```

### 6. Actualizar Celdas de Tabla

```php
// Actualizar una celda específica de una tabla
$service = new TableDemoService();

// Por ID de celda
$service->updateComponentCache(83561270, [
    'text' => 'New Value',
    'align' => 'right'
]);

// Por nombre de celda (si está nombrada)
$service->updateComponentCache('0_1', [  // Fila 0, Columna 1
    'text' => 'Updated Cell'
]);
```

### 7. Actualizar en Broadcast/WebSocket

```php
class NotificationBroadcast implements ShouldBroadcast
{
    public function handle()
    {
        $service = new NotificationService();
        
        // Actualizar badge de notificaciones
        $service->updateComponentCache('notification_badge', [
            'badge' => $this->unreadCount,
            'style' => $this->unreadCount > 0 ? 'danger' : 'default'
        ]);
    }
}
```

## Método Alternativo: `updateCache()`

Si ya tienes el `$container` cargado y has modificado componentes, puedes usar:

```php
$service->updateCache();
```

Este método guarda el estado actual del container en caché, pero requiere que hayas cargado y modificado el container previamente.

## Ventajas

✅ **Inmediato**: Los cambios se reflejan instantáneamente en caché  
✅ **Eficiente**: Modifica el JSON directamente sin reconstruir componentes  
✅ **Simple**: API clara con array de propiedades  
✅ **Flexible**: Funciona con ID o nombre de componente  
✅ **Confiable**: Lanza excepciones claras si el componente no existe  

## Notas Importantes

1. **Solo propiedades existentes**: No puedes agregar propiedades que no existen en el tipo de componente
2. **Sin validación de tipo**: Las propiedades no se validan, asegúrate de pasar valores correctos
3. **Cache TTL**: Los cambios persisten según el TTL configurado (default 30 minutos)
4. **Por usuario**: Cada usuario tiene su propio caché de UI
5. **Búsqueda por nombre**: Solo funciona si el componente tiene nombre asignado

## Diferencia con el Flujo Normal de Eventos

### Flujo Normal (Eventos UI)
```
1. initializeEventContext() - Carga container
2. onEventHandler() - Modifica componentes
3. finalizeEventContext() - Detecta cambios, actualiza caché, retorna diff
```

### Actualización Directa (Nueva funcionalidad)
```
1. updateComponentCache() - Modifica JSON en caché directamente
```

El nuevo método es útil cuando NO estás en un flujo de evento UI pero necesitas actualizar la interfaz.
