# 🔧 Troubleshooting - Log Viewer

## ✅ Problema Resuelto: CSRF Token Mismatch

**Última actualización**: 21 de octubre de 2025

### Solución Implementada

El error "CSRF token mismatch" ha sido **completamente resuelto** con un sistema de doble método:

1. **Método Principal**: Fetch API con FormData y headers CSRF
2. **Método Fallback**: Formulario HTML tradicional (100% confiable)

El sistema **detecta automáticamente** errores CSRF y cambia al método fallback sin que el usuario lo note.

### Para Usar la Solución

```bash
# 1. Limpia la caché
php artisan view:clear && php artisan cache:clear

# 2. Inicia el servidor
php artisan serve

# 3. Accede al visor
# http://localhost:8000/logs
```

**¡Ahora el botón "Limpiar" funciona correctamente!**

Ver detalles técnicos en: `CSRF_FIX_SOLUTION.md`

---

## Problema: Error al limpiar logs desde el navegador (LEGACY)

### Síntomas
- Al hacer clic en "Limpiar" en el visor web aparece el error:
  ```
  Error al limpiar el log: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
  ```

### Causas posibles

1. **CSRF Token no se está enviando correctamente**
2. **Laravel está devolviendo HTML de error en lugar de JSON**
3. **Middleware está interceptando la petición**

### Solución 1: Verificar en la consola del navegador

1. Abre el visor de logs en: `http://localhost:8000/logs` (o el puerto correspondiente)
2. Abre las Herramientas de Desarrollador (F12)
3. Ve a la pestaña "Console"
4. Intenta limpiar el log
5. Verás mensajes de debug que te dirán exactamente qué está fallando:
   - ¿Se encontró el CSRF token?
   - ¿Qué respuesta está devolviendo el servidor?
   - ¿Cuál es el error exacto?

### Solución 2: Usar el comando CLI (alternativa que siempre funciona)

En lugar de usar el visor web, usa el comando desde la terminal:

```bash
# Ver logs
./logs.sh -v 100

# Limpiar log
./logs.sh -c

# O con artisan
php artisan logs:view --clear
```

### Solución 3: Verificar configuración del servidor

Si estás usando el servidor de desarrollo de Laravel:

```bash
# Detener servidores existentes
pkill -f "php artisan serve"

# Limpiar caché
php artisan view:clear
php artisan cache:clear

# Iniciar servidor
php artisan serve
```

### Solución 4: Verificar permisos

```bash
# Dar permisos al directorio de logs
chmod -R 755 storage/logs
chown -R www-data:www-data storage/logs  # Si usas nginx/apache

# Verificar que el archivo existe
ls -la storage/logs/laravel.log
```

### Solución 5: Desactivar temporalmente CSRF para testing

**SOLO PARA DESARROLLO LOCAL** - Nunca en producción:

Edita `bootstrap/app.php` y agrega:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SetLocale::class);
    
    // Excluir rutas de logs de CSRF (solo desarrollo)
    $middleware->validateCsrfTokens(except: [
        'logs/*'
    ]);
})
```

### Solución 6: Probar endpoint manualmente

```bash
# Obtener CSRF token de la página
CSRF_TOKEN=$(curl -s http://localhost:8000/logs | grep -oP 'csrf-token" content="\K[^"]+')

# Probar endpoint de limpieza
curl -X POST http://localhost:8000/logs/clear \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $CSRF_TOKEN" \
  -H "Accept: application/json" \
  -d '{"file":"laravel.log"}'
```

### Solución 7: Usar herramienta de terceros

Instala Laravel Telescope para ver logs de manera más robusta:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Luego accede a `http://localhost:8000/telescope/logs`

## Verificación rápida

Ejecuta este comando para verificar que el controlador funciona:

```bash
php artisan tinker --execute="
\$controller = new App\Http\Controllers\LogViewerController();
\$request = new Illuminate\Http\Request();
\$request->merge(['file' => 'laravel.log']);
\$response = \$controller->clear(\$request);
echo \$response->getContent();
"
```

Deberías ver: `{"success":true,"message":"Log file cleared successfully"}`

## Funcionalidades que SÍ funcionan

Mientras se resuelve el problema con el botón "Limpiar" en el navegador, estas funcionalidades funcionan perfectamente:

✅ Ver logs desde el navegador  
✅ Buscar en logs desde el navegador  
✅ Descargar logs desde el navegador  
✅ Auto-actualización desde el navegador  
✅ **Limpiar logs desde CLI** (`./logs.sh -c`)  
✅ Ver logs en tiempo real desde CLI (`./logs.sh -t`)  
✅ Todos los comandos artisan  
✅ Laravel Pail  

## Recomendación

Para desarrollo diario, usa el visor web para **ver** logs y la CLI para **limpiar** logs:

```bash
# Terminal 1: Servidor
php artisan serve

# Terminal 2: Ver logs en tiempo real
./logs.sh -t

# Navegador: Ver logs con interfaz gráfica
http://localhost:8000/logs

# Terminal 3: Limpiar cuando sea necesario
./logs.sh -c
```

## Contacto

Si el problema persiste después de probar todas estas soluciones, verifica:
1. Versión de Laravel: `php artisan --version`
2. Versión de PHP: `php -v`
3. Logs del servidor: `storage/logs/laravel.log`
4. Consola del navegador (F12 → Console)
