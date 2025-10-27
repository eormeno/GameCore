# Diálogo TIMEOUT - Resumen de Implementación

## Descripción
Sistema de diálogos con auto-cierre temporal que soporta cuenta regresiva visible en tiempo real.

## Archivos Creados/Modificados

### Backend

1. **`app/Services/UI/Enums/TimeUnit.php`** (NUEVO)
   - Enum con 4 unidades: SECONDS, MINUTES, HOURS, DAYS
   - Métodos de conversión y formateo de etiquetas

2. **`app/Services/UI/Enums/DialogType.php`** (MODIFICADO)
   - Agregado caso `TIMEOUT`
   - Configuración del ícono ⏱️ y comportamiento

3. **`app/Services/UI/Modals/ConfirmDialogService.php`** (MODIFICADO)
   - Soporte para parámetros de timeout
   - Generación de metadatos para el frontend
   - Label de countdown opcional

4. **`app/Services/Screens/DemoMenuService.php`** (MODIFICADO)
   - 2 ejemplos de timeout: 10 segundos y 5 minutos
   - Handlers para los diálogos de ejemplo

### Frontend

5. **`public/js/ui-renderer.js`** (MODIFICADO)
   - Función `openModal()` detecta timeout
   - Función `startModalCountdown()` inicia la cuenta regresiva
   - Función `getRemainingValue()` calcula tiempo restante
   - Función `getSingularLabel()` formatea etiquetas
   - Función `executeTimeoutAction()` ejecuta acción al completarse
   - Limpieza automática de timers en `closeModal()`

### Documentación

6. **`docs/dialog-types-guide.md`** (MODIFICADO)
   - Sección completa del tipo TIMEOUT
   - Ejemplos de uso para cada unidad de tiempo
   - Casos de uso y mejores prácticas

## Parámetros del Tipo TIMEOUT

```php
type: DialogType::TIMEOUT,
timeout: int,              // Cantidad de tiempo (requerido)
timeUnit: TimeUnit,        // SECONDS|MINUTES|HOURS|DAYS (default: SECONDS)
showCountdown: bool,       // Mostrar cuenta regresiva (default: true)
timeoutAction: string,     // Acción al completarse (default: 'close_modal')
```

## Flujo de Funcionamiento

### Backend
1. Service recibe acción para mostrar timeout dialog
2. `ConfirmDialogService` construye el diálogo con metadatos
3. Agrega `_timeout`, `_time_unit`, `_timeout_ms`, etc.
4. Retorna estructura con `parent: 'modal'`

### Frontend
1. `openModal()` detecta `_timeout_ms` en el config
2. Renderiza el modal normalmente
3. Si `showCountdown: true`, inicia `startModalCountdown()`
4. Timer actualiza label cada 100ms
5. Al llegar a 0, ejecuta `executeTimeoutAction()`
6. Si es 'close_modal', cierra; si es custom, llama al backend

## Características Especiales

### Cuenta Regresiva en Tiempo Real
- Actualización cada 100ms (suave y fluida)
- Usa `Date.now()` para evitar deriva
- Formato automático singular/plural
- Limpieza automática al cerrar

### Unidades de Tiempo
- **SECONDS**: Ideal para notificaciones breves (3-30 seg)
- **MINUTES**: Para timeouts de sesión (1-60 min)
- **HOURS**: Para licencias temporales (1-24 hrs)
- **DAYS**: Para períodos de prueba (1-365 días)

### Acciones Personalizadas
El parámetro `timeoutAction` permite ejecutar cualquier handler del service:
```php
timeoutAction: 'extend_session'  // En lugar de solo cerrar
```

## Ejemplos de Uso

### Notificación Temporal (3 segundos)
```php
$confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Guardado",
    message: "Cambios guardados exitosamente",
    timeout: 3,
    timeUnit: TimeUnit::SECONDS,
    showCountdown: false,
    callerServiceId: $serviceId
);
```

### Advertencia de Sesión (2 minutos)
```php
$confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Sesión por Expirar",
    message: "Tu sesión expirará en:",
    timeout: 2,
    timeUnit: TimeUnit::MINUTES,
    showCountdown: true,
    timeoutAction: 'session_expired',
    callerServiceId: $serviceId
);
```

### Licencia Temporal (7 días)
```php
$confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Período de Prueba",
    message: "Tu licencia de evaluación expira en:",
    timeout: 7,
    timeUnit: TimeUnit::DAYS,
    showCountdown: true,
    callerServiceId: $serviceId
);
```

## Casos de Uso Recomendados

1. **Notificaciones de Éxito**: 2-5 segundos, sin countdown
2. **Warnings de Sesión**: 1-5 minutos, con countdown
3. **Licencias Temporales**: 1-30 días, con countdown
4. **Procesos en Progreso**: 3-10 segundos, sin countdown
5. **Recordatorios**: Variable según contexto

## Pruebas

Desde el menú de la aplicación:
1. **Components > Test Timeout (10s)** - Prueba de 10 segundos
2. **Components > Test Timeout (5min)** - Prueba de 5 minutos

Observa:
- ✅ Cuenta regresiva actualizada en tiempo real
- ✅ Formato correcto (singular/plural)
- ✅ Cierre automático al llegar a 0
- ✅ Botón "Cerrar" permite cierre manual

## Notas Técnicas

- El timer usa `setInterval` con 100ms para fluidez
- Se limpia automáticamente al cerrar el modal
- Soporta cierre manual antes del timeout
- La acción puede ser 'close_modal' o cualquier handler del service
- El countdown muestra valores redondeados hacia arriba (ceil)
