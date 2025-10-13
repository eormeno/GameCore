# Sistema de Internacionalización (i18n)

Este proyecto implementa un sistema simple y eficiente de internacionalización usando un **único archivo CSV** como fuente de verdad.

## Características

- ✅ **CSV como única fuente de verdad** - No más sincronización entre archivos
- ✅ **Servicio de traducción optimizado** con caché en memoria
- ✅ **Middleware de detección automática** de idioma del usuario
- ✅ **Edición directa del CSV** - Los cambios se reflejan inmediatamente
- ✅ **Soporte multiidioma** (inglés/español configurado)
- ✅ **Búsqueda inteligente** por módulos
- ✅ **Fallbacks automáticos** cuando no existe traducción
- ✅ **Sin dependencias externas** - Solo PHP nativo

## Estructura de Archivos

```
storage/app/translations/
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

### En Controladores

```php
class GameAppController extends Controller
{
    protected TranslationService $translationService;
    
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
        // Pre-cargar módulos frecuentes
        $this->translationService->preloadModules(['games', 'errors']);
    }
    
    public function someMethod()
    {
        // Traducción simple
        $message = $this->translationService->translate('game_not_found');
        
        // Traducción con parámetros
        $message = $this->translationService->translate('resource_not_found', [
            'resource' => 'image.png'
        ]);
        
        // Ejemplos más avanzados con parámetros
        $welcomeMessage = $this->translationService->translate('user_welcome', [
            'name' => Auth::user()->name
        ]);
        
        $scoreMessage = $this->translationService->translate('score_achieved', [
            'points' => $game->score,
            'time' => $game->duration
        ]);
        
        $progressMessage = $this->translationService->translate('level_progress', [
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

### Opciones de Init
- `--file=nombre.csv` - Nombre del archivo CSV
- `--force` - Sobrescribir archivo existente sin preguntar

## Flujo de Trabajo

1. **Inicializar**: `php artisan translation:init`
2. **Editar**: Abrir `storage/app/translations/translations.csv` en tu editor favorito
3. **Usar**: Las traducciones están disponibles inmediatamente

### Agregar nuevas traducciones

Solo añade nuevas filas al CSV:
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

## Ventajas del Sistema

- **Simplicidad**: Un solo archivo para todas las traducciones
- **Sin sincronización**: No hay archivos duplicados que puedan desactualizarse
- **Edición universal**: Cualquier herramienta puede editar CSV
- **Sin dependencias**: Solo usa funciones nativas de PHP
- **Performance**: Caché en memoria para acceso rápido
- **Flexibilidad**: Fácil de versionar y hacer backups