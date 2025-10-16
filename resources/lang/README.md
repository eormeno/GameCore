# Archivos de Traducción

## translations.csv

Este archivo contiene **todas las traducciones** del sistema en formato CSV.

### Características
- **Única fuente de verdad** para todas las traducciones
- **Trackeado por Git** para control de versiones
- **Editable** con cualquier herramienta (Excel, Google Sheets, VSCode)
- **Actualización inmediata** sin necesidad de recompilar

### Estructura del CSV

```csv
Module,Key,Slug,EN,ES
games,welcome,games.welcome,"Welcome to the game!","¡Bienvenido al juego!"
```

- **Module**: Módulo al que pertenece la traducción (games, errors, common, etc.)
- **Key**: Clave única dentro del módulo
- **Slug**: Identificador completo usado en el código (module.key)
- **EN**: Traducción en inglés
- **ES**: Traducción en español

### Uso en el Código

```php
// Traducción simple
t('games.welcome')

// Traducción con parámetros
t('games.user_welcome', ['name' => 'Carlos'])

// Traducción por lotes
tb(['common.save', 'common.cancel', 'common.delete'])
```

### Comandos Artisan

```bash
# Agregar nueva traducción
php artisan translation:add games new_feature "New Feature" --es="Nueva Funcionalidad"

# Ordenar archivo CSV
php artisan translation:sort

# Inicializar archivo con traducciones base
php artisan translation:init
```

### Documentación Completa

Ver `/docs/i18n-system.md` para documentación completa del sistema de traducciones.
