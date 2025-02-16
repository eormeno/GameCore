# Pasos para instalar el proyecto
## Clonar el repositorio
```
git clone https://github.com/eormeno/GameCore
```
Ubicarse en la carpeta del proyecto
```bash
cd GameCore
```
## Instalar dependencias
```bash
composer install
```
## Crear el archivo .env
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

##  Crear la clave de la aplicación
```bash
php artisan key:generate
```

## Crear la base de datos y ejecutar las migraciones
```bash
php artisan migrate --force --seed
```
Posteriormente, ejecute el comando para migrar los objetos de juego, prefabs, y servicios.
```bash
php artisan games
```

## Iniciar el servidor
```bash
php artisan serve
```

