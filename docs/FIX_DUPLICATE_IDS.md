# Solución: IDs Duplicados en Componentes UI

## Fecha: 17 de Octubre de 2025

---

## 🔴 Problema Identificado

Los elementos UI (containers y components) estaban generando **IDs duplicados** porque cada clase (`UIContainer`, `UIComponent`, `BaseUIBuilder`) tenía su propio array estático `$autoIncPerContext`, lo que causaba que:

```php
// Antes del fix:
Container ID: 1    ❌ DUPLICADO
Button 1 ID: 1     ❌ DUPLICADO
Button 2 ID: 2     ✅ OK
```

### Causa Raíz:

```php
// UIComponent.php
class UIComponent {
    private static array $autoIncPerContext = [];  // Array SEPARADO
    // ...
}

// UIContainer.php
class UIContainer {
    private static array $autoIncPerContext = [];  // Array SEPARADO (duplica contadores)
    // ...
}
```

Ambas clases empezaban su contador en 0 para cada contexto, resultando en colisiones de IDs.

---

## ✅ Solución Implementada

### 1. **Clase Centralizada: `UIIdGenerator`**

Se creó una clase singleton que maneja **todos** los IDs del sistema UI:

**Archivo:** `app/Services/UI/Support/UIIdGenerator.php`

```php
class UIIdGenerator
{
    /** @var array<string, int> Contador único compartido por TODOS los componentes */
    private static array $autoIncPerContext = [];

    /**
     * Genera un ID único para cualquier elemento UI
     */
    public static function generate(string $context): int
    {
        if (!isset(self::$autoIncPerContext[$context])) {
            self::$autoIncPerContext[$context] = 0;
        }

        $localId = ++self::$autoIncPerContext[$context];
        $offset = self::getContextOffset($context);

        return $offset + $localId;
    }

    /**
     * Información de debugging
     */
    public static function getContextInfo(string $context): array
    {
        return [
            'context' => $context,
            'offset' => self::getContextOffset($context),
            'current_count' => self::$autoIncPerContext[$context] ?? 0,
        ];
    }

    /**
     * Reset para testing
     */
    public static function reset(): void
    {
        self::$autoIncPerContext = [];
    }

    /**
     * Offset único por contexto (CRC32 hash)
     */
    private static function getContextOffset(string $context): int
    {
        if ($context === 'default') {
            return 0;
        }

        $hash = crc32($context);
        $offset = (abs($hash) % 9999) * 10000;

        return $offset;
    }
}
```

### 2. **Actualización de `UIComponent`**

```php
// ANTES
private static array $autoIncPerContext = [];

public function __construct(?string $name = null)
{
    // ... código de inicialización local
    if (!isset(self::$autoIncPerContext[$context])) {
        self::$autoIncPerContext[$context] = 0;
    }
    $localId = ++self::$autoIncPerContext[$context];
    $offset = self::getContextOffset($context);
    $this->id = $offset + $localId;
}

// DESPUÉS
use App\Services\UI\Support\UIIdGenerator;

public function __construct(?string $name = null)
{
    $context = $this->detectCallingContext();
    
    // ✅ Usar generador centralizado
    $this->id = UIIdGenerator::generate($context);
    
    // ... resto del código
}
```

### 3. **Actualización de `UIContainer`**

```php
// ANTES
private static array $autoIncPerContext = [];

public function __construct(?string $name = null)
{
    // ... código duplicado de generación de ID
}

// DESPUÉS
use App\Services\UI\Support\UIIdGenerator;

public function __construct(?string $name = null)
{
    $context = $this->detectCallingContext();
    
    // ✅ Usar generador centralizado
    $this->id = UIIdGenerator::generate($context);
    
    // ... resto del código
}
```

### 4. **Actualización de `BaseUIBuilder`**

```php
// ANTES
private static array $autoIncPerContext = [];

public function __construct(?string $name = null)
{
    // ... código duplicado
}

// DESPUÉS
use App\Services\UI\Support\UIIdGenerator;

public function __construct(?string $name = null)
{
    $context = $this->detectCallingContext();
    
    // ✅ Usar generador centralizado
    $this->id = UIIdGenerator::generate($context);
    
    // ... resto del código
}
```

---

## 📊 Resultados

### ✅ Antes del Fix:
```json
{
    "1": {                        // Container
        "type": "container",
        "elements": {
            "1": {                // ❌ Button con ID duplicado
                "type": "button"
            },
            "2": {
                "type": "button"
            }
        }
    }
}
```

### ✅ Después del Fix:
```json
{
    "1": {                        // Container
        "type": "container",
        "elements": {
            "2": {                // ✅ Button con ID único
                "type": "button"
            },
            "3": {                // ✅ Button con ID único
                "type": "button"
            }
        }
    }
}
```

### Ejemplo Real (GameLobbyScreenService):
```
32600001: game_lobby_screen (container) ✅
  32600002: new_game (button) ✅
  32600003: warning_message (label) ✅
  32600007: saved_games (table) ✅
    Row 1:
      32600004: actions container ✅
        32600005: play_1_game (button) ✅
        32600006: delete_1_game (button) ✅
```

**Todos los IDs son únicos y secuenciales** ✅

---

## 🧪 Tests de Verificación

### Test Simple:
```bash
php test_duplicate_ids.php
```
**Resultado:**
```
IDs encontrados: 1, 2, 3
Total IDs: 3
IDs únicos: 3
✅ Todos los IDs son únicos
```

### Test Exhaustivo (estructura anidada):
```bash
php test_exhaustive_ids.php
```
**Resultado:**
```
IDs generados: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12
Total de IDs: 12
IDs únicos: 12
✅ ÉXITO: Todos los IDs son únicos
✅ Los IDs son completamente secuenciales
```

### Tests de Integración:
```bash
./test.ps1 bba  # ✅ 4 passed
./test.ps1 cnt  # ✅ 5 passed
```

---

## 🎯 Ventajas de la Solución

### 1. **Centralización**
- Un solo punto de control para todos los IDs
- Eliminación de código duplicado
- Más fácil de mantener

### 2. **Garantía de Unicidad**
- Todos los componentes usan el mismo contador
- Imposible tener colisiones dentro del mismo contexto
- IDs secuenciales predecibles

### 3. **Facilidad de Testing**
- Método `reset()` para limpiar contadores en tests
- Método `getContextInfo()` para debugging
- Tests independientes y repetibles

### 4. **Backward Compatibility**
- Los métodos `getContextInfo()` siguen funcionando
- La API pública no cambia
- Código existente sigue funcionando sin modificaciones

---

## 📝 Archivos Modificados

1. ✅ **Creado:** `app/Services/UI/Support/UIIdGenerator.php`
2. ✅ **Modificado:** `app/Services/UI/Components/UIComponent.php`
3. ✅ **Modificado:** `app/Services/UI/Components/UIContainer.php`
4. ✅ **Modificado:** `app/Services/UI/Components/BaseUIBuilder.php`

---

## 🔧 Cambios en el Código

### Eliminado de todas las clases:
```php
private static array $autoIncPerContext = [];

private static function getContextOffset(string $context): int
{
    // Código movido a UIIdGenerator
}
```

### Agregado a todas las clases:
```php
use App\Services\UI\Support\UIIdGenerator;

// En el constructor:
$this->id = UIIdGenerator::generate($context);

// En getContextInfo():
public static function getContextInfo(string $context): array
{
    return UIIdGenerator::getContextInfo($context);
}
```

---

## ✨ Beneficios Adicionales

1. **DRY (Don't Repeat Yourself)**: Código de generación de IDs centralizado
2. **Single Responsibility**: UIIdGenerator tiene una única responsabilidad
3. **Testability**: Más fácil probar la lógica de generación de IDs
4. **Debugging**: Método centralizado para inspeccionar el estado
5. **Extensibilidad**: Fácil agregar nuevas estrategias de generación

---

## 🎓 Conclusión

El problema de **IDs duplicados** está **completamente resuelto** mediante:

- ✅ Centralización de la generación de IDs en `UIIdGenerator`
- ✅ Eliminación de contadores duplicados en clases separadas
- ✅ Garantía de unicidad a través de un contador compartido
- ✅ Compatibilidad con código existente
- ✅ Tests pasando correctamente

**Estado:** ✅ **RESUELTO Y VERIFICADO**

**Última Actualización:** 17 de Octubre de 2025
