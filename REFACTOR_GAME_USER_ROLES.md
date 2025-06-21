# Refactorización de Roles de GameUser

## Cambios Realizados

### 1. Creación del Enum GameUserRole
- **Archivo**: `app/Enums/GameUserRole.php`
- **Descripción**: Nuevo enum que define los roles de usuario en las partidas
- **Roles disponibles**:
  - `OWNER`: Propietario de la partida
  - `ADMINISTRATOR`: Administrador con permisos delegados
  - `TESTER`: Usuario con acceso especial para pruebas
  - `PLAYER`: Jugador regular (rol por defecto)

### 2. Refactorización de la Migración
- **Archivo**: `database/migrations/11_create_game_user_table.php`
- **Cambios**:
  - Eliminados campos: `is_owner`, `is_administrator`, `is_tester`
  - Agregado campo: `role` (string, default: 'player')
- **Nota**: Se modificó directamente la migración base, por lo que no se requiere migración adicional

### 3. Actualización del Modelo GameUser
- **Archivo**: `app/Models/GameUser.php`
- **Cambios principales**:
  - Agregado cast para `role` como `GameUserRole`
  - Actualizados `$fillable` para incluir `role`
  - Refactorizados métodos de verificación de roles
  - Agregados nuevos métodos: `isOwner()`, `isAdministrator()`, `isTester()`, `isPlayer()`
  - Agregado método `hasAdministrativePrivileges()`
  - Agregado método `canManageRole()` para jerarquía de permisos
  - Agregados scopes: `byRole()`, `administrative()`
  - Mantenidas constantes para compatibilidad con código existente

### 4. Actualización del Servicio
- **Archivo**: `app/Services/GameInstanceService.php`
- **Cambios**:
  - Actualizado para usar `GameUserRole::OWNER` en lugar de `is_owner = true`
  - Cambiadas referencias de `$gu->is_owner` a `$gu->isOwner()`

### 5. Actualización de Documentación
- **Archivo**: `app/Models/GameUser.md`
- **Cambios**: Documentación completamente actualizada con nuevos métodos y ejemplos

## Ventajas de la Refactorización

### 1. **Mejor Organización**
- Un solo campo `role` en lugar de múltiples campos boolean
- Enum tipado que previene errores
- Jerarquía clara de permisos

### 2. **Facilidad de Extensión**
- Agregar nuevos roles solo requiere modificar el enum
- No necesita cambios en la estructura de la base de datos

### 3. **Mejor Mantenimiento**
- Código más limpio y legible
- Métodos específicos para cada tipo de verificación
- Lógica centralizada en el enum

### 4. **Compatibilidad Hacia Atrás**
- Se mantienen las constantes existentes para no romper código legacy
- La estructura de la tabla se define correctamente desde el inicio

## Métodos Disponibles

### Verificación de Roles
```php
$gameUser->hasRole(GameUserRole::OWNER)
$gameUser->isOwner()
$gameUser->isAdministrator()
$gameUser->isTester()
$gameUser->isPlayer()
$gameUser->hasAdministrativePrivileges()
$gameUser->canManageRole($targetRole)
$gameUser->getRoleName()
```

### Scopes de Consulta
```php
GameUser::owners()
GameUser::administrators()
GameUser::testers()
GameUser::byRole(GameUserRole::ADMINISTRATOR)
GameUser::administrative()
```

### Métodos del Enum
```php
GameUserRole::default()
GameUserRole::administrativeRoles()
GameUserRole::all()
$role->isAdministrative()
$role->isOwner()
$role->isHigherThan($otherRole)
```

## Instrucciones de Aplicación

1. **Ejecutar la migración**:
   ```bash
   php artisan migrate
   ```

2. **Verificar la migración** (opcional):
   ```bash
   php artisan migrate:status
   ```

## Notas Importantes

- La migración ya incluye la estructura correcta con el campo `role`
- Los métodos antiguos siguen funcionando por compatibilidad
- Se recomienda actualizar gradualmente el código para usar los nuevos métodos
- Las constantes antiguas (`STATUS_ACTIVE`, `JOIN_AUTO`, etc.) se mantienen para compatibilidad
