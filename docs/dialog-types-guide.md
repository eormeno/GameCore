# Guía de Tipos de Diálogo

## Descripción General

El sistema de diálogos ahora soporta múltiples tipos definidos en el enum `DialogType`. Cada tipo tiene características específicas como íconos predeterminados, estilos de botones y configuración de botones.

## Tipos de Diálogo Disponibles

### 1. INFO - Diálogo de Información
**Propósito:** Mostrar información o notificaciones al usuario.

**Características:**
- 🔵 Ícono: ℹ️ (información)
- 🔘 Un solo botón: "OK" (estilo primary)
- ✅ No tiene botón de cancelar

**Ejemplo de uso:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::INFO,
    title: "Información",
    message: "Los cambios se han guardado correctamente.",
    confirmAction: 'close_dialog',
    callerServiceId: $serviceId
);
```

---

### 2. CONFIRM - Diálogo de Confirmación
**Propósito:** Confirmar acciones importantes o destructivas.

**Características:**
- 🔵 Ícono: ❓ (pregunta)
- 🔘 Dos botones: "Cancelar" (secondary) + "Confirmar" (danger)
- ✅ Tiene botón de cancelar

**Ejemplo de uso:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::CONFIRM,
    title: "Eliminar Usuario",
    message: "¿Estás seguro de que deseas eliminar este usuario?",
    confirmAction: 'delete_user',
    confirmParams: ['user_id' => 123],
    cancelAction: 'cancel_delete',
    callerServiceId: $serviceId
);
```

---

### 3. WARNING - Diálogo de Advertencia
**Propósito:** Advertir al usuario antes de realizar una acción potencialmente peligrosa.

**Características:**
- 🟡 Ícono: ⚠️ (advertencia)
- 🔘 Dos botones: "Cancelar" (secondary) + "Continuar" (warning)
- ✅ Tiene botón de cancelar

**Ejemplo de uso:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::WARNING,
    title: "Configuración",
    message: "¿Quieres resetear la configuración? Esta acción no se puede deshacer.",
    confirmAction: 'reset_settings',
    cancelAction: 'cancel_settings',
    callerServiceId: $serviceId
);
```

---

### 4. ERROR - Diálogo de Error
**Propósito:** Mostrar errores al usuario.

**Características:**
- 🔴 Ícono: ❌ (error)
- 🔘 Un solo botón: "Entendido" (estilo primary)
- ✅ No tiene botón de cancelar

**Ejemplo de uso:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::ERROR,
    title: "Error de conexión",
    message: "No se pudo conectar con el servidor. Por favor, intenta nuevamente.",
    confirmAction: 'close_error',
    callerServiceId: $serviceId
);
```

---

### 5. SUCCESS - Diálogo de Éxito
**Propósito:** Confirmar que una operación se completó exitosamente.

**Características:**
- 🟢 Ícono: ✅ (éxito)
- 🔘 Un solo botón: "OK" (estilo primary)
- ✅ No tiene botón de cancelar

**Ejemplo de uso:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::SUCCESS,
    title: "¡Completado!",
    message: "La operación se realizó correctamente.",
    confirmAction: 'close_success',
    callerServiceId: $serviceId
);
```

---

### 6. CHOICE - Diálogo de Elección Múltiple
**Propósito:** Presentar múltiples opciones al usuario.

**Características:**
- 🔵 Ícono: 🤔 (pensando)
- 🔘 Botones personalizados (definidos por el desarrollador)
- ✅ Tiene botón de cancelar (si se incluye)

**Ejemplo de uso:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::CHOICE,
    title: "Guardar cambios",
    message: "Tienes cambios sin guardar. ¿Qué deseas hacer?",
    buttons: [
        [
            'label' => 'Guardar',
            'action' => 'save_changes',
            'params' => ['draft' => false],
            'style' => 'primary'
        ],
        [
            'label' => 'Guardar como Borrador',
            'action' => 'save_changes',
            'params' => ['draft' => true],
            'style' => 'secondary'
        ],
        [
            'label' => 'Descartar',
            'action' => 'discard_changes',
            'params' => [],
            'style' => 'danger'
        ]
    ],
    callerServiceId: $serviceId
);
```

---

### 7. TIMEOUT - Diálogo con Auto-cierre ⏱️
**Propósito:** Mostrar mensajes temporales que se autocierran después de un tiempo específico.

**Características:**
- 🔵 Ícono: ⏱️ (reloj)
- 🔘 Un solo botón: "Cerrar" (estilo primary) - opcional si el usuario quiere cerrar antes
- ⏱️ Cuenta regresiva visible en tiempo real (opcional)
- 🔄 Se autocierra cuando el tiempo se agota
- ✅ No tiene botón de cancelar

**Parámetros especiales:**
- `timeout` (int, requerido): Cantidad de tiempo
- `timeUnit` (TimeUnit enum): Unidad de tiempo (SECONDS, MINUTES, HOURS, DAYS)
- `showCountdown` (bool): Mostrar contador visible (default: true)
- `showCloseButton` (bool): Mostrar botón de cerrar manualmente (default: true)
- `timeoutAction` (string): Acción a ejecutar al completarse (default: 'close_modal')

**Ejemplo de uso - Segundos:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Notificación Temporal",
    message: "Este mensaje se autodestruirá en:",
    timeout: 10,
    timeUnit: TimeUnit::SECONDS,
    showCountdown: true,
    confirmAction: 'close_dialog',
    callerServiceId: $serviceId
);
```

**Ejemplo de uso - Minutos:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Sesión de Prueba",
    message: "Tu sesión temporal expirará en:",
    timeout: 5,
    timeUnit: TimeUnit::MINUTES,
    showCountdown: true,
    timeoutAction: 'session_expired',
    callerServiceId: $serviceId
);
```

**Ejemplo de uso - Horas:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Licencia Temporal",
    message: "Tu licencia de evaluación expira en:",
    timeout: 24,
    timeUnit: TimeUnit::HOURS,
    showCountdown: true,
    callerServiceId: $serviceId
);
```

**Ejemplo de uso - Días:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Período de Prueba",
    message: "Tu período de prueba finaliza en:",
    timeout: 7,
    timeUnit: TimeUnit::DAYS,
    showCountdown: true,
    callerServiceId: $serviceId
);
```

**Ejemplo sin countdown visible:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Guardando...",
    message: "Los cambios se están guardando automáticamente.",
    timeout: 3,
    timeUnit: TimeUnit::SECONDS,
    showCountdown: false, // No mostrar cuenta regresiva
    timeoutAction: 'close_modal',
    callerServiceId: $serviceId
);
```

**Ejemplo sin botón de cerrar:**
```php
$confirmService = app(ConfirmDialogService::class);
$modalUI = $confirmService->getUI(
    type: DialogType::TIMEOUT,
    title: "Auto-cierre",
    message: "Este diálogo se cerrará automáticamente en:",
    timeout: 5,
    timeUnit: TimeUnit::SECONDS,
    showCountdown: true,
    showCloseButton: false, // No mostrar botón de cerrar
    callerServiceId: $serviceId
);
```

---

## Parámetros Comunes

Todos los tipos de diálogo aceptan los siguientes parámetros:

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `type` | `DialogType` | No | Tipo de diálogo (default: CONFIRM) |
| `title` | `string` | No | Título del diálogo |
| `message` | `string` | No | Mensaje a mostrar |
| `icon` | `string` | No | Emoji personalizado (usa default si no se especifica) |
| `confirmAction` | `string` | Sí | Acción a ejecutar al confirmar |
| `confirmParams` | `array` | No | Parámetros adicionales para la acción |
| `confirmLabel` | `string` | No | Etiqueta del botón (usa default si no se especifica) |
| `cancelAction` | `string` | No | Acción a ejecutar al cancelar (default: 'close_modal') |
| `cancelLabel` | `string` | No | Etiqueta del botón cancelar |
| `callerServiceId` | `string` | Sí | ID del servicio que abrió el diálogo |
| `buttons` | `array` | No* | Botones personalizados (solo para CHOICE) |
| `timeout` | `int` | No** | Tiempo para auto-cierre (solo para TIMEOUT) |
| `timeUnit` | `TimeUnit` | No | Unidad de tiempo: SECONDS, MINUTES, HOURS, DAYS (default: SECONDS) |
| `showCountdown` | `bool` | No | Mostrar cuenta regresiva visible (default: true) |
| `showCloseButton` | `bool` | No | Mostrar botón de cerrar manualmente (default: true) |
| `timeoutAction` | `string` | No | Acción al completarse el timeout (default: 'close_modal') |

\* Requerido solo para tipo `CHOICE`
\*\* Requerido solo para tipo `TIMEOUT`

---

## Enum TimeUnit

El enum `TimeUnit` define las unidades de tiempo disponibles para diálogos TIMEOUT:

### Valores Disponibles

- `TimeUnit::SECONDS` - Segundos
- `TimeUnit::MINUTES` - Minutos  
- `TimeUnit::HOURS` - Horas
- `TimeUnit::DAYS` - Días

### Métodos

#### `getSingularLabel(): string`
Retorna la etiqueta en singular ('segundo', 'minuto', 'hora', 'día').

#### `getPluralLabel(): string`
Retorna la etiqueta en plural ('segundos', 'minutos', 'horas', 'días').

#### `getLabel(int $quantity): string`
Retorna la etiqueta apropiada según la cantidad (singular o plural).

#### `toMilliseconds(int $value): int`
Convierte el valor a milisegundos para el temporizador JavaScript.

---

## Métodos del Enum DialogType

El enum `DialogType` proporciona métodos útiles:

### `getDefaultIcon(): string`
Retorna el emoji por defecto para el tipo de diálogo.

### `getConfirmButtonStyle(): string`
Retorna el estilo del botón de confirmación ('primary', 'danger', 'warning').

### `hasCancelButton(): bool`
Indica si el tipo de diálogo debe mostrar botón de cancelar.

### `getDefaultConfirmLabel(): string`
Retorna la etiqueta por defecto para el botón de confirmación.

### `getDefaultCancelLabel(): string`
Retorna la etiqueta por defecto para el botón de cancelar.

---

## Compatibilidad con Versiones Anteriores

El servicio mantiene compatibilidad con el formato anterior:

```php
// ✅ Formato anterior (aún funciona)
$modalUI = $confirmService->getUI(
    title: "Confirmar",
    message: "¿Estás seguro?",
    icon: 'question',
    confirmAction: 'confirm',
    callerServiceId: $serviceId
);

// ✅ Formato nuevo (recomendado)
$modalUI = $confirmService->getUI(
    type: DialogType::CONFIRM,
    title: "Confirmar",
    message: "¿Estás seguro?",
    confirmAction: 'confirm',
    callerServiceId: $serviceId
);
```

---

## Mejores Prácticas

1. **Usa el tipo apropiado:** Cada tipo está diseñado para un propósito específico.
2. **Mensajes claros:** Sé específico en los mensajes y títulos.
3. **Acciones descriptivas:** Nombra las acciones de forma clara (ej: `delete_user`, no `action1`).
4. **ServiceId siempre:** Nunca olvides pasar `callerServiceId` para que los callbacks funcionen.
5. **Personaliza cuando sea necesario:** Puedes sobrescribir íconos y etiquetas cuando el default no sea adecuado.

---

## Estilos de Botones Disponibles

- `primary` - Azul (acción principal)
- `secondary` - Gris (acción secundaria)
- `danger` - Rojo (acciones destructivas)
- `warning` - Amarillo (advertencias)
- `success` - Verde (confirmaciones positivas)

---

## Ejemplos Completos en DemoMenuService

Ver `app/Services/Screens/DemoMenuService.php` para ejemplos funcionales de:
- **WARNING**: Item "Settings" - Advertencia antes de resetear configuración
- **INFO**: Item "About" - Información del sistema
- **ERROR**: Item "Test Error Dialog" - Simulación de error de conexión
- **TIMEOUT**: Item "Test Timeout (10s)" - Cuenta regresiva de 10 segundos
- **TIMEOUT**: Item "Test Timeout (5min)" - Cuenta regresiva de 5 minutos

## Casos de Uso del Tipo TIMEOUT

### 1. Notificaciones Temporales
Mostrar mensajes que desaparecen automáticamente:
```php
timeout: 5,
timeUnit: TimeUnit::SECONDS,
showCountdown: false
```

### 2. Cuenta Regresiva de Sesión
Advertir al usuario que su sesión está por expirar:
```php
timeout: 2,
timeUnit: TimeUnit::MINUTES,
showCountdown: true,
timeoutAction: 'extend_session' // Permite extender la sesión
```

### 3. Licencias/Períodos de Prueba
Mostrar tiempo restante de una licencia temporal:
```php
timeout: 30,
timeUnit: TimeUnit::DAYS,
showCountdown: true
```

### 4. Procesos en Progreso
Mostrar mientras se completa un proceso automático:
```php
timeout: 3,
timeUnit: TimeUnit::SECONDS,
showCountdown: false,
message: "Guardando cambios..."
```

---

## Características Técnicas del Tipo TIMEOUT

### Frontend
- **Actualización suave**: El contador se actualiza cada 100ms para transiciones fluidas
- **Precisión**: Usa `Date.now()` para evitar deriva del temporizador
- **Limpieza automática**: Los timers se limpian al cerrar el modal
- **Formato inteligente**: Muestra singular/plural según el valor (1 segundo vs 2 segundos)

### Backend
- **Conversión automática**: Convierte el valor a milisegundos según la unidad
- **Metadatos incluidos**: `_timeout`, `_time_unit`, `_timeout_ms`, etc.
- **Acción personalizable**: Puede ejecutar cualquier acción del servicio al completarse

---
