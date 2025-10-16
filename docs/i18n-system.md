# Sistema de Internacionalización (i18n)

Este proyecto implementa un sistema simple y eficiente de internacionalización usando un **único archivo CSV** como fuente de verdad.

## 📍 Ubicación del Archivo

**Archivo principal**: `resources/lang/translations.csv`

Esta ubicación fue elegida porque:
- ✅ Es semánticamente correcta (recursos de idioma)
- ✅ Está fuera de `.gitignore` y puede ser trackeada por Git
- ✅ Sigue las convenciones de Laravel para recursos de idioma
- ✅ Es fácil de compartir entre desarrolladores y traductores
- ✅ Permite versionado completo de las traducciones

## Características

- ✅ **CSV como única fuente de verdad** - No más sincronización entre archivos
- ✅ **Trackeado por Git** - Control de versiones completo para traducciones
- ✅ **Servicio de traducción optimizado** con caché en memoria
- ✅ **Middleware de detección automática** de idioma del usuario
- ✅ **Edición directa del CSV** - Los cambios se reflejan inmediatamente
- ✅ **Soporte multiidioma** (inglés/español configurado)
- ✅ **Búsqueda inteligente** por módulos
- ✅ **Fallbacks automáticos** cuando no existe traducción
- ✅ **Sin dependencias externas** - Solo PHP nativo

## Estructura de Archivos

```
resources/
└── lang/
    └── translations.csv   # Única fuente de verdad para todas las traducciones
```

### Estructura del CSV
```csv
Module,Key,Slug,EN,ES
common,welcome,common.welcome,Welcome,Bienvenido
errors,game_not_found,errors.game_not_found,Game not found,Juego no encontrado
games,new_game_created,games.new_game_created,New game created,Nuevo juego creado
```

## Uso Básico

### Función Helper Global `t()`

La forma más simple de usar traducciones es con la función helper `t()`:

```php
class GameAppController extends Controller
{
    public function someMethod()
    {
        // Traducción simple
        $message = t('game_not_found');
        
        // Traducción con parámetros
        $message = t('resource_not_found', ['resource' => 'image.png']);
        
        // Ejemplos más avanzados con parámetros
        $welcomeMessage = t('user_welcome', ['name' => Auth::user()->name]);
        
        $scoreMessage = t('score_achieved', [
            'points' => $game->score,
            'time' => $game->duration
        ]);
        
        $progressMessage = t('level_progress', [
            'current' => $user->current_level,
            'total' => $game->total_levels
        ]);
        
        return response()->json([
            'welcome' => $welcomeMessage,
            'score' => $scoreMessage,
            'progress' => $progressMessage
        ]);
    }
}
```

### Funciones Helper Disponibles

```php
// Traducción simple
t('welcome_message')

// Traducción con parámetros
t('user_welcome', ['name' => 'Carlos'])

// Traducción por lotes (más eficiente para múltiples slugs)
tb(['save', 'cancel', 'delete', 'edit'])

// Limpiar caché de traducciones
t_clear_cache()

// Ver reporte de traducciones faltantes
t_missing()
```

### Detección de Idioma

El middleware `SetLocale` detecta automáticamente el idioma en este orden:

1. **Preferencia del usuario autenticado** (campo `locale` en tabla `users`)
2. **Parámetro de request** (`?locale=es`)
3. **Header Accept-Language** del navegador
4. **Idioma por defecto** (configurado en `config/app.php`)

## Gestión de Traducciones

### Inicializar archivo CSV

```bash
# Crear archivo CSV con traducciones base
php artisan translation:init

# Sobrescribir archivo existente
php artisan translation:init --force

# Crear con nombre personalizado
php artisan translation:init --file=my_translations.csv
```

### Edición directa

El archivo CSV puede editarse directamente con cualquier editor:
- **Excel** - Abrir como archivo CSV
- **Google Sheets** - Importar archivo CSV
- **LibreOffice Calc** - Abrir archivo CSV
- **Editor de texto** - Para cambios rápidos

Los cambios se reflejan **inmediatamente** sin necesidad de comandos adicionales.

## Configuración

### config/app.php
```php
'available_locales' => ['en', 'es'],
```

### config/translation.php
```php
'search_modules' => [
    'games', 'common', 'errors', 'validation', 'messages'
],
'cache_enabled' => true,
'cache_ttl' => 3600,
'csv' => [
    'default_filename' => 'translations.csv',
    'backup_on_import' => true,
    'delimiter' => ',',
],
```

## Comandos Disponibles

| Comando | Descripción |
|---------|-------------|
| `translation:init` | Inicializa archivo CSV con traducciones base |
| `translation:add` | Agrega una nueva traducción al archivo CSV |
| `translation:sort` | Ordena físicamente el archivo CSV por módulo y clave |

### Comando `translation:init`
```bash
php artisan translation:init [--file=nombre.csv] [--force]
```
**Opciones:**
- `--file=nombre.csv` - Nombre del archivo CSV
- `--force` - Sobrescribir archivo existente sin preguntar

### Comando `translation:add`
```bash
php artisan translation:add {module} {key} {english} [opciones]
```
**Argumentos:**
- `module` - Nombre del módulo (ej: games, errors, common)  
- `key` - Clave de traducción (ej: new_feature, welcome_back)
- `english` - Texto en inglés

**Opciones:**
- `--file=nombre.csv` - Archivo CSV a usar
- `--slug=custom.slug` - Slug personalizado (por defecto: module.key)
- `--copy-to-all` - Copiar texto inglés a todos los idiomas
- `--es="texto"` - Traducción en español (opcional)

**Ejemplos:**
```bash
# Agregar con traducción en español
php artisan translation:add games high_score "New High Score!" --es="¡Nueva Puntuación Máxima!"

# Copiar inglés a todos los idiomas
php artisan translation:add errors timeout "Request timeout" --copy-to-all

# Usar slug personalizado  
php artisan translation:add ui submit "Submit" --slug="forms.submit_btn"
```

### Comando `translation:sort`
```bash
php artisan translation:sort [--file=nombre.csv]
```
Ordena el archivo CSV alfabéticamente por módulo y luego por clave para mejor organización.

## Flujo de Trabajo

1. **Inicializar**: `php artisan translation:init`
2. **Agregar traducciones**: 
   - Comando: `php artisan translation:add module key "English text"`
   - Manual: Editar `storage/app/translations/translations.csv`
3. **Ordenar** (opcional): `php artisan translation:sort`
4. **Usar**: Las traducciones están disponibles inmediatamente

### Agregar nuevas traducciones

**Opción 1: Comando Artisan (Recomendado)**
```bash
# Con traducción en español
php artisan translation:add games victory "You Won!" --es="¡Ganaste!"

# Copiar inglés a otros idiomas
php artisan translation:add errors timeout "Connection timeout" --copy-to-all
```

**Opción 2: Edición manual del CSV**
```csv
Module,Key,Slug,EN,ES
messages,user_login,messages.user_login,User logged in,Usuario inició sesión
messages,user_logout,messages.user_logout,User logged out,Usuario cerró sesión
```

### Traducciones con Parámetros

Para incluir parámetros dinámicos, usa la sintaxis `:parameter` en el CSV:

```csv
Module,Key,Slug,EN,ES
messages,user_welcome,messages.user_welcome,"Welcome back, :name!","¡Bienvenido de vuelta, :name!"
messages,score_achieved,messages.score_achieved,"You scored :points points in :time seconds","Obtuviste :points puntos en :time segundos"
messages,level_progress,messages.level_progress,"Level :current of :total completed","Nivel :current de :total completado"
errors,file_size_error,errors.file_size_error,"File :filename is too large (:size MB). Maximum allowed: :max MB","El archivo :filename es muy grande (:size MB). Máximo permitido: :max MB"
```

**Uso en código:**
```php
$service->translate('user_welcome', ['name' => 'Carlos']);
$service->translate('score_achieved', ['points' => 1500, 'time' => 45]);
$service->translate('file_size_error', [
    'filename' => 'image.jpg',
    'size' => 15,
    'max' => 10
]);
```

## Ejemplos de Uso

### Traducción Simple
```php
$service->translate('welcome_message')
// EN: "Welcome to the game!"
// ES: "¡Bienvenido al juego!"
```

### Traducción con Parámetros
```php
// Ejemplo simple
$service->translate('resource_not_found', ['resource' => 'background.jpg'])
// EN: "Resource background.jpg not found"
// ES: "Recurso background.jpg no encontrado"

// Ejemplos más complejos
$service->translate('user_welcome', ['name' => 'Carlos'])
// EN: "Welcome back, Carlos!"
// ES: "¡Bienvenido de vuelta, Carlos!"

$service->translate('score_achieved', ['points' => 1500, 'time' => 45])
// EN: "You scored 1500 points in 45 seconds"
// ES: "Obtuviste 1500 puntos en 45 segundos"

$service->translate('file_size_error', [
    'filename' => 'image.jpg',
    'size' => 15,
    'max' => 10
])
// EN: "File image.jpg is too large (15 MB). Maximum allowed: 10 MB"
// ES: "El archivo image.jpg es muy grande (15 MB). Máximo permitido: 10 MB"
```

### Traducción por Lotes
```php
$translations = $service->translateBatch([
    'welcome',
    'loading',
    'save'
]);
```

### Búsqueda por Módulo
```php
// Busca primero en games.welcome, luego en otros módulos
$service->translate('welcome')

// Usa directamente games.welcome
$service->translate('games.welcome')
```

## Performance

- **Caché en memoria** para evitar búsquedas repetidas
- **Pre-carga de módulos** frecuentes
- **Singleton service** para reutilizar instancia
- **Búsqueda optimizada** por módulos

## Migración de Usuarios

El sistema incluye una migración que añade el campo `locale` a la tabla `users`:

```sql
ALTER TABLE users ADD COLUMN locale VARCHAR(5) DEFAULT 'en' AFTER email;
```

## Troubleshooting

### Error: "Translation not found"
- Verifica que el archivo de idioma existe en `lang/{locale}/`
- Asegúrate de que la clave existe en el módulo correcto
- Usa `translation:export` para ver todas las traducciones disponibles

### Error al leer CSV
- Verifica que el archivo CSV existe en `storage/app/translations/`
- Asegúrate de que tiene las columnas correctas: Module, Key, Slug, EN, ES
- Revisa que no hay caracteres especiales que rompan el formato CSV
- Reinicia el caché: `$service->clearCache()`

### Performance lenta
- Activa caché: `TRANSLATION_CACHE_ENABLED=true`
- Pre-carga módulos frecuentes en constructores
- Considera usar `translateBatch()` para múltiples traducciones

## Contribuir

1. Edita directamente `storage/app/translations/translations.csv`
2. Añade nuevas filas con el formato: `Module,Key,Slug,EN,ES`
3. Los cambios se reflejan inmediatamente
4. Opcional: Comparte el CSV con traductores para colaboración

## Funciones Helper Globales

El sistema incluye funciones helper que simplifican el uso de traducciones:

### `t($slug, $replace = [])`
Función principal para traducir un slug con parámetros opcionales:

```php
// Traducción simple
t('welcome_message')
// "Welcome!" / "¡Bienvenido!"

// Con parámetros
t('user_welcome', ['name' => 'Carlos'])
// "Welcome back, Carlos!" / "¡Bienvenido de vuelta, Carlos!"
```

### `tb($slugs)`
Traducción por lotes para mejor performance:

```php
$translations = tb(['save', 'cancel', 'delete', 'edit']);
// ['save' => 'Save', 'cancel' => 'Cancel', ...]
```

### `t_clear_cache()`
Limpia el caché de traducciones:

```php
t_clear_cache();
// Cache cleared
```

### `t_missing()`
Muestra reporte de traducciones faltantes:

```php
t_missing();
// Missing translations: game_start, level_complete
```

### Ventajas de las Funciones Helper

- **Código más limpio**: No necesitas inyectar el servicio
- **Uso universal**: Disponibles en cualquier parte del código
- **Sintaxis simple**: Similar a otros helpers de Laravel como `__()` o `trans()`
- **Zero configuration**: Funcionan automáticamente sin setup

## Ventajas del Sistema

- **Simplicidad**: Un solo archivo para todas las traducciones
- **Sin sincronización**: No hay archivos duplicados que puedan desactualizarse
- **Edición universal**: Cualquier herramienta puede editar CSV
- **Sin dependencias**: Solo usa funciones nativas de PHP
- **Performance**: Caché en memoria para acceso rápido
- **Flexibilidad**: Fácil de versionar y hacer backups
- **Helpers globales**: Funciones simples disponibles en toda la aplicación