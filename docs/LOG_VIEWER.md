# 🔍 Sistema de Visualización de Logs - GameCore

El proyecto GameCore ahora cuenta con un sistema completo para visualizar y gestionar logs de la aplicación.

## 📋 Características

- ✅ Visualización de logs desde el navegador web
- ✅ Comandos Artisan para gestionar logs
- ✅ Script Bash con múltiples opciones
- ✅ Búsqueda y filtrado de logs
- ✅ Auto-actualización en tiempo real
- ✅ Descarga de archivos de log
- ✅ Limpieza de logs
- ✅ Resaltado de sintaxis por nivel (error, warning, info, debug)

## 🌐 Visor Web de Logs

### Acceso
Inicia el servidor y accede a:
```
http://localhost:8000/logs
```

### Funcionalidades del visor web:
- 📁 Seleccionar diferentes archivos de log
- 🔢 Configurar número de líneas a mostrar
- 🔍 Buscar términos específicos
- 🔄 Auto-actualización cada 5 segundos
- ⬇️ Descargar archivos de log
- 🗑️ Limpiar archivos de log
- 🎨 Resaltado de errores, warnings e info con colores

## 🖥️ Comando Artisan

### Uso básico
```bash
# Ver las últimas 50 líneas
php artisan logs:view

# Ver las últimas 100 líneas
php artisan logs:view --lines=100

# Seguir logs en tiempo real
php artisan logs:view --tail

# Limpiar el archivo de log
php artisan logs:view --clear

# Especificar un archivo de log diferente
php artisan logs:view --file=custom.log --lines=200
```

### Opciones disponibles
- `--lines=N` : Número de líneas a mostrar (por defecto: 50)
- `--tail` : Seguir el log en tiempo real
- `--clear` : Limpiar el archivo de log
- `--file=NAME` : Especificar archivo de log (por defecto: laravel.log)

## 🚀 Script Bash (logs.sh)

### Instalación del comando global
Para usar el comando `logs` desde cualquier directorio:
```bash
sudo ln -sf $(pwd)/logs.sh /usr/local/bin/logs
```

### Uso del script local
```bash
# Ver las últimas 50 líneas (por defecto)
./logs.sh

# Ver las últimas 100 líneas
./logs.sh -v 100
./logs.sh --view 100

# Seguir logs en tiempo real
./logs.sh -t
./logs.sh --tail

# Buscar un término en los logs
./logs.sh -s "error"
./logs.sh --search "UserNotFoundException"

# Ver solo errores
./logs.sh -e
./logs.sh --errors

# Ver solo advertencias
./logs.sh -w
./logs.sh --warnings

# Limpiar el archivo de log
./logs.sh -c
./logs.sh --clear

# Usar un archivo de log específico
./logs.sh -f custom.log -v 100
./logs.sh --file custom.log --tail

# Usar Laravel Pail (requiere paquete instalado)
./logs.sh -p
./logs.sh --pail

# Ver ayuda
./logs.sh -h
./logs.sh --help
```

### Características del script:
- 🎨 Colorización automática:
  - 🔴 Rojo para errores y excepciones
  - 🟡 Amarillo para advertencias
  - 🔵 Azul para información
- 🔍 Búsqueda con resaltado
- 📊 Filtrado por nivel (errors, warnings)
- 🔄 Seguimiento en tiempo real
- 🧹 Limpieza segura con confirmación

## 🛠️ Laravel Pail

El proyecto ya tiene Laravel Pail instalado. Para usarlo:

```bash
# Desde composer
composer run logs

# Desde artisan
php artisan pail --timeout=0

# Desde el script
./logs.sh -p
```

Laravel Pail ofrece:
- 🎨 Colorización avanzada
- 🔍 Filtrado en tiempo real
- 📊 Formateo mejorado de logs
- ⚡ Rendimiento optimizado

## 📂 Archivos del Sistema

### Controlador Web
`app/Http/Controllers/LogViewerController.php`
- Gestiona las peticiones del visor web
- API para obtener, descargar y limpiar logs

### Vista Blade
`resources/views/logs/viewer.blade.php`
- Interfaz web moderna con tema oscuro
- JavaScript para interactividad y auto-actualización

### Comando Artisan
`app/Console/Commands/ViewLogsCommand.php`
- Comando CLI para gestionar logs

### Script Bash
`logs.sh`
- Script completo con múltiples opciones

### Rutas
`routes/web.php`
- Rutas para el visor web:
  - `GET /logs` - Vista principal
  - `GET /logs/content` - Obtener contenido
  - `GET /logs/download` - Descargar log
  - `POST /logs/clear` - Limpiar log

## 🎯 Casos de Uso

### Desarrollo
```bash
# Terminal 1: Servidor
./start.sh

# Terminal 2: Logs en tiempo real
./logs.sh -t

# O usar Pail
./logs.sh -p
```

### Debugging
```bash
# Buscar errores específicos
./logs.sh -s "UserNotFoundException"

# Ver solo errores
./logs.sh -e

# Ver las últimas 200 líneas
./logs.sh -v 200
```

### Producción
```bash
# Ver logs en el navegador
# Accede a: https://tu-dominio.com/logs

# Descargar logs para análisis
# Usa el botón "Descargar" en el visor web
```

### Mantenimiento
```bash
# Limpiar logs antiguos
./logs.sh -c

# O desde el visor web con el botón "Limpiar"
```

## 🔒 Seguridad

**IMPORTANTE:** El visor web de logs NO tiene autenticación por defecto. Para producción:

1. Agrega middleware de autenticación en `routes/web.php`:
```php
Route::prefix('logs')->middleware(['auth'])->group(function () {
    // rutas de logs
});
```

2. O restringe acceso por IP en tu servidor web (nginx/apache)

3. O desactiva las rutas en producción y usa solo comandos CLI

## 📊 Configuración de Logs

Edita `config/logging.php` para configurar:
- Canal por defecto
- Nivel de log (debug, info, warning, error)
- Rotación de logs (daily, single)
- Retención de logs

Ejemplo para logs diarios:
```php
'default' => env('LOG_CHANNEL', 'daily'),

'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => env('LOG_DAILY_DAYS', 14),
],
```

## 🎓 Tips

1. **Usa búsqueda para debugging**: `./logs.sh -s "nombreDeClase"`
2. **Tail en desarrollo**: `./logs.sh -t` en una terminal separada
3. **Limpia regularmente**: Evita que los logs crezcan demasiado
4. **Usa niveles apropiados**: debug, info, warning, error, critical
5. **Laravel Pail es mejor para desarrollo**: Más rápido y con mejor formato

## 🔗 Enlaces útiles

- [Documentación de Laravel Logging](https://laravel.com/docs/11.x/logging)
- [Laravel Pail](https://github.com/laravel/pail)
- [Monolog (motor de logs)](https://github.com/Seldaek/monolog)

## 🐛 Troubleshooting

### El visor web no carga logs
- Verifica permisos: `chmod -R 755 storage/logs`
- Verifica que exista el archivo de log

### Script bash no funciona
- Hazlo ejecutable: `chmod +x logs.sh`
- Verifica que estés en el directorio del proyecto

### No se pueden limpiar logs
- Verifica permisos de escritura en `storage/logs`
- Usa: `sudo chown -R www-data:www-data storage/logs`

---

**Desarrollado para GameCore** 🎮
