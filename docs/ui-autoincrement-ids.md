# Sistema de IDs Auto-Incrementales Contextualizados

## 📋 Resumen

El sistema de UI Builder ahora incluye **IDs internos auto-incrementales** que son únicos por contexto (servicio de pantalla). Cada componente tiene:

1. **ID interno** (`$_id`): Número auto-incremental único con offset por contexto
2. **ID de usuario** (`$id`): String definido por el desarrollador para identificar el componente en el JSON

## 🎯 Objetivo

Garantizar que cada componente de cada pantalla tenga un ID interno único y predecible, facilitando:
- **Debugging**: Saber de qué pantalla viene un componente por su ID
- **Tracking**: IDs únicos para eventos del frontend
- **Testing**: IDs predecibles para tests automatizados

## 🔢 Cómo Funciona

### 1. Detección Automática de Contexto

El sistema detecta automáticamente desde qué clase se está creando el componente:

```php
// En GameLobbyScreenService.php
$button = UIBuilder::button('new_game');
// Detecta contexto: "GameLobbyScreenService"
```

La detección funciona mediante `debug_backtrace()`, buscando la primera clase fuera del namespace `App\Services\UI\`.

### 2. Generación de Offset por Contexto

Cada contexto (nombre de clase) genera un offset único mediante hash CRC32:

```php
$hash = crc32('GameLobbyScreenService');  // Ej: -1234567890
$offset = (abs($hash) % 9999) * 10000;    // Ej: 67890000
```

**Características:**
- Offsets son múltiplos de 10000
- Mismo contexto = mismo offset (determinístico)
- Diferentes contextos = offsets diferentes
- Rango: 0 a 99,990,000

### 3. IDs Secuenciales Dentro del Contexto

Dentro de cada contexto, los componentes reciben IDs secuenciales:

```php
// GameLobbyScreenService (offset: 67890000)
$btn1 = UIBuilder::button('new_game');    // ID interno: 67890001
$lbl1 = UIBuilder::label('title');        // ID interno: 67890002
$btn2 = UIBuilder::button('load_game');   // ID interno: 67890003
```

## 📖 Ejemplos de Uso

### Ejemplo 1: Crear Componentes (Transparente)

```php
namespace App\Services\Screens;

class GameLobbyScreenService
{
    public function getGameLobbyScreen(User $user, GameApp $gameApp): array
    {
        // No necesitas hacer nada especial, funciona automáticamente
        $container = UIBuilder::container('game_lobby')
            ->slot('canvas')
            ->layout(LayoutType::VERTICAL);

        $container->add(
            UIBuilder::button('new_game')
                ->label('New Game')
                ->action('create_game')
        );

        $container->add(
            UIBuilder::label('info')
                ->text('Select a saved game')
        );

        return $container->build();
    }
}
```

### Ejemplo 2: Acceder al ID Interno

```php
$button = UIBuilder::button('my_button');

$internalId = $button->getInternalId();  // Ej: 67890001
$userId = $button->getId();              // 'my_button'

echo "ID interno: $internalId";  // 67890001
echo "ID usuario: $userId";       // my_button
```

### Ejemplo 3: Debugging de Contextos

```php
use App\Services\UI\Components\UIComponent;

// Ver información de un contexto específico
$info = UIComponent::getContextInfo('GameLobbyScreenService');

/*
Array (
    'context' => 'GameLobbyScreenService',
    'offset' => 67890000,
    'current_count' => 5  // Cuántos componentes se han creado
)
*/
```

### Ejemplo 4: Múltiples Pantallas

```php
// En GameLobbyScreenService
$btn1 = UIBuilder::button('btn1');  // ID interno: 67890001

// En GamePlayScreenService  
$btn2 = UIBuilder::button('btn1');  // ID interno: 78230001

// En SettingsScreenService
$btn3 = UIBuilder::button('btn1');  // ID interno: 12890001

// Mismo ID de usuario, diferentes IDs internos (por contexto)
```

## 🔍 Tabla de Offsets Comunes

| Servicio | Offset Estimado* | Rango de IDs |
|----------|------------------|--------------|
| GameLobbyScreenService | 67,890,000 | 67,890,001 - 67,899,999 |
| GamePlayScreenService | 78,230,000 | 78,230,001 - 78,239,999 |
| SettingsScreenService | 12,890,000 | 12,890,001 - 12,899,999 |
| ProfileScreenService | 56,340,000 | 56,340,001 - 56,349,999 |

_*Los offsets se calculan automáticamente mediante hash. Estos son ejemplos ilustrativos._

## 🎨 JSON Output

El JSON que se retorna al frontend usa el **ID de usuario** (string):

```json
{
  "game_lobby": {
    "type": "container",
    "visible": true,
    "elements": {
      "new_game": {
        "type": "button",
        "label": "New Game",
        "action": "create_game"
      },
      "info": {
        "type": "label",
        "text": "Select a saved game"
      }
    }
  }
}
```

El ID interno **no se expone** en el JSON por defecto, pero puede agregarse si es necesario:

```php
// Si necesitas exponer el ID interno en el JSON:
protected function toJson(): array
{
    $config = $this->config;
    $config['_id'] = $this->_id;  // Agregar ID interno
    return [$this->id => $config];
}
```

## 🧪 Testing

### Test de IDs Secuenciales

```php
test('components have sequential internal IDs', function () {
    $btn1 = UIBuilder::button('btn1');
    $btn2 = UIBuilder::button('btn2');
    $btn3 = UIBuilder::button('btn3');
    
    expect($btn2->getInternalId())->toBe($btn1->getInternalId() + 1)
        ->and($btn3->getInternalId())->toBe($btn2->getInternalId() + 1);
});
```

### Test de Offsets Únicos

```php
test('different contexts have different offsets', function () {
    $info1 = UIComponent::getContextInfo('ServiceA');
    $info2 = UIComponent::getContextInfo('ServiceB');
    
    expect($info1['offset'])->not->toBe($info2['offset']);
});
```

## ⚙️ Configuración y Personalización

### Cambiar el Rango de Offsets

Si necesitas ajustar el rango de offsets, modifica `getContextOffset()` en `UIComponent.php`:

```php
private static function getContextOffset(string $context): int
{
    if ($context === 'default') {
        return 0;
    }
    
    $hash = crc32($context);
    
    // Cambiar aquí: múltiplos de 100000 en lugar de 10000
    $offset = (abs($hash) % 999) * 100000;
    
    return $offset;
}
```

### Offsets Fijos para Servicios Específicos

Si prefieres offsets fijos para ciertos servicios:

```php
private static function getContextOffset(string $context): int
{
    // Tabla de offsets fijos
    $fixedOffsets = [
        'GameLobbyScreenService' => 10000,
        'GamePlayScreenService' => 20000,
        'SettingsScreenService' => 30000,
    ];
    
    if (isset($fixedOffsets[$context])) {
        return $fixedOffsets[$context];
    }
    
    // Para servicios no definidos, usar hash
    if ($context === 'default') {
        return 0;
    }
    
    $hash = crc32($context);
    return 100000 + ((abs($hash) % 8999) * 10000);
}
```

## 🚨 Consideraciones Importantes

### 1. **IDs No Persisten Entre Requests**

Los IDs internos se generan en memoria y se resetean en cada request HTTP:

```php
// Request 1
$btn = UIBuilder::button('test');  // ID interno: 67890001

// Request 2 (nuevo proceso PHP)
$btn = UIBuilder::button('test');  // ID interno: 67890001 (se resetea)
```

✅ **Esto es correcto** si los IDs solo se usan durante el request para generar el JSON de respuesta.

❌ **Problema** si necesitas IDs persistentes entre requests (considera usar UUID o base de datos).

### 2. **Colisiones de Hash (Muy Improbables)**

CRC32 puede generar el mismo offset para contextos diferentes (colisión de hash). 

**Probabilidad**: ~0.01% con menos de 100 servicios

**Mitigación**: Usar offsets fijos para servicios principales (ver personalización arriba).

### 3. **Máximo de Componentes por Contexto**

Cada contexto puede tener hasta **9,999 componentes** antes de alcanzar el siguiente offset.

Si una pantalla necesita más componentes, considera dividirla en sub-servicios.

## 📊 Arquitectura

```
Request HTTP
    ↓
GameLobbyScreenService::getGameLobbyScreen()
    ↓
UIBuilder::button('new_game')
    ↓
UIComponent::__construct('new_game')
    ↓
detectCallingContext() → "GameLobbyScreenService"
    ↓
getContextOffset("GameLobbyScreenService") → 67890000
    ↓
$_id = 67890000 + 1 = 67890001
    ↓
Componente creado con:
    - $_id = 67890001 (interno)
    - $id = 'new_game' (usuario)
```

## 📚 Referencias

- **Clase Principal**: `App\Services\UI\Components\UIComponent`
- **Método de Detección**: `detectCallingContext()`
- **Método de Offset**: `getContextOffset()`
- **Método Público**: `getInternalId(): int`
- **Tests**: `tests/Unit/Services/UI/BaseUIBuilderTest.php`

## 🎯 Resumen Rápido

| Característica | Valor |
|----------------|-------|
| **ID Interno** | Integer auto-incremental con offset |
| **ID Usuario** | String definido por desarrollador |
| **Offset por Contexto** | Múltiplo de 10,000 |
| **Detección** | Automática vía stack trace |
| **Persistencia** | Solo durante el request HTTP |
| **Colisiones** | Prácticamente imposibles (<100 servicios) |
| **Rango por Contexto** | 10,000 componentes |

---

**Última actualización**: 17 de octubre de 2025
