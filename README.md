# GameCore - Plataforma de Juegos

Sistema de backend para juegos multijugador con UI Builder dinámico.

## 📚 Documentación

- **[Sistema UI](docs/UI_SYSTEM.md)** - Documentación completa del UI Builder
- **[Visor de Logs](docs/LOG_VIEWER.md)** - Sistema de visualización y gestión de logs
- **[Migración Slot→Parent](docs/slot-to-parent-migration.md)** - Guía de actualización
- **[Refactoring de Headers](docs/table-header-refactoring.md)** - Sistema de headers de tablas
- **[Sistema i18n](docs/i18n-system.md)** - Internacionalización
- **[Sistema de Menús](docs/menu-system.md)** - Menús dinámicos
- **[Componentes de Formulario](docs/form-component.md)** - Formularios

## Pasos para instalar el proyecto
### Clonar el repositorio
```
git clone https://github.com/eormeno/GameCore
```
Ubicarse en la carpeta del proyecto
```bash
cd GameCore
```
## Instalación local para desarrollo
### Instalar dependencias
```bash
composer install
```
### Crear el archivo .env
En base al archivo .env.example, crear un archivo .env haciendo una copia.
```bash
cp .env.example .env
```
Luego configurar las variables de entorno en el archivo .env, en especial el nombre y clave del usuario raíz, y el de los usuarios falsos.
Inicialmente, tras copiar el archivo .env.example, las variables de entorno de la base de datos estarán configuradas con los valores por defecto.

```bash
FAKE_USERS_PASSWORD=CHANGEME
ADMIN_USERNAME="Admin"
ADMIN_EMAIL=admin@gamecore.com
ADMIN_PASSWORD=CHANGEME
```
Modifique las claves de los usuarios falsos y del usuario raíz que dicen CHANGEME por una clave segura.

###  Crear la clave de la aplicación
```bash
php artisan key:generate
```

### Crear la base de datos y ejecutar las migraciones
```bash
php artisan migrate --force --seed
```
Posteriormente, ejecute el comando para migrar los objetos de juego, prefabs, y servicios.
```bash
php artisan games
```
### Iniciar el servidor
```bash
php artisan serve
```

## 🔍 Visualización de Logs

GameCore incluye un sistema completo para visualizar y gestionar logs. Ver **[Documentación del Visor de Logs](docs/LOG_VIEWER.md)** para más detalles.

### Acceso rápido:

**Visor Web:**
```bash
# Inicia el servidor y accede a http://localhost:8000/logs
php artisan serve
```

**Comandos en Terminal:**
```bash
# Ver logs con el script bash
./logs.sh -v 100          # Ver últimas 100 líneas
./logs.sh -t              # Seguir logs en tiempo real
./logs.sh -s "error"      # Buscar errores
./logs.sh -p              # Usar Laravel Pail

# Ver logs con artisan
php artisan logs:view --lines=100
php artisan logs:view --tail
php artisan pail --timeout=0
```

## Comandos de Desarrollo (Linux)

En sistemas Linux, se han creado scripts de bash equivalentes a los scripts de PowerShell para facilitar el desarrollo:

### Scripts de migración

#### Script migrate.sh
El archivo `migrate.sh` permite resetear y migrar la base de datos:

```bash
# Ejecutar localmente
./migrate.sh
```

**Funcionalidad:**
- Elimina la base de datos SQLite existente (`database/database.sqlite`)
- Ejecuta las migraciones con seed (`php artisan migrate --force --seed`)
- Ejecuta el comando de configuración de juegos (`php artisan games`)

#### Comando global migrate
```bash
# Uso del comando global
migrate
```

### Scripts de servidor

#### Script start.sh
El archivo `start.sh` permite iniciar el servidor con opciones de reseteo de base de datos:

```bash
# Ejecutar localmente
./start.sh           # Solo inicia el servidor
./start.sh -r        # Resetea la base de datos e inicia el servidor
```

**Funcionalidad:**
- **Sin opciones**: Inicia directamente el servidor Laravel (`php artisan serve`)
- **Con opción `-r`**: 
  - Para MySQL: Elimina y recrea la base de datos usando las credenciales del `.env`
  - Para SQLite: Elimina el archivo `database/database.sqlite`
  - Ejecuta migraciones con seed
  - Ejecuta configuración de juegos
  - Inicia el servidor

#### Comando global start
```bash
# Uso del comando global
start             # Solo inicia el servidor
start -r          # Resetea la base de datos e inicia el servidor
```

### Scripts de testing

#### Script test.sh
El archivo `test.sh` permite ejecutar tests de manera selectiva:

```bash
# Ejecutar localmente
./test.sh           # Ejecuta todos los tests
./test.sh auth      # Ejecuta AuthTest
./test.sh cnt       # Ejecuta CNTPlayGameTest
./test.sh gtn       # Ejecuta GTNPlayGameTest
./test.sh bba       # Ejecuta BBAPlayGameTest
./test.sh mtq       # Ejecuta MTQPlayGameTest
```

#### Comando global gametest
```bash
# Uso del comando global
gametest          # Ejecuta todos los tests
gametest auth     # Ejecuta AuthTest
gametest cnt      # Ejecuta CNTPlayGameTest
gametest gtn      # Ejecuta GTNPlayGameTest
gametest bba      # Ejecuta BBAPlayGameTest
gametest mtq      # Ejecuta MTQPlayGameTest
```

### Filtros de testing disponibles
- `auth` - Ejecuta las pruebas de autenticación
- `cnt` - Ejecuta las pruebas del juego CNT (Connect)
- `gtn` - Ejecuta las pruebas del juego GTN (Guess The Number)
- `bba` - Ejecuta las pruebas del juego BBA (Brick Breaker Arcade)
- `mtq` - Ejecuta las pruebas del juego MTQ (Math Quiz)

### Configuración de enlaces simbólicos
Los enlaces simbólicos permiten ejecutar los scripts desde cualquier directorio:

```bash
# Crear todos los enlaces simbólicos (requiere permisos de administrador)
ln -sf $(pwd)/migrate.sh /usr/local/bin/migrate
ln -sf $(pwd)/start.sh /usr/local/bin/start
ln -sf $(pwd)/test.sh /usr/local/bin/gametest
```

## Instalación para producción en un servidor Ubuntu
### Requisitos
- PHP. con las extensiones habilitadas en php.ini (openssl, pdo_mysql, mbstring, etc.).
- Composer
- Nginx
- MySQL
