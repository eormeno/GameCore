# GameUser Model Documentation

## Propósito

El modelo `GameUser` representa la relación entre un usuario (`User`) y una partida (`Game`). Esta tabla pivote extendida maneja no solo la asociación, sino también el estado del usuario en la partida, sus roles, y el historial de acciones.

## Estructura de la Tabla

```sql
game_user:
├── id (Primary Key)
├── game_id (Foreign Key → games.id)
├── user_id (Foreign Key → users.id)
├── is_owner (Boolean) - Si es el creador/dueño de la partida
├── is_administrator (Boolean) - Si tiene permisos administrativos
├── is_tester (Boolean) - Si es un tester de la partida
├── status (Enum) - Estado actual del usuario en la partida
├── join_method (Enum) - Cómo se unió a la partida
├── actioned_by (Foreign Key → users.id) - Quién ejecutó la última acción
├── reason (Text) - Razón de kick/ban
├── joined_at (Timestamp) - Cuándo se volvió activo
├── left_at (Timestamp) - Cuándo abandonó/fue expulsado
└── timestamps (created_at, updated_at)
```

## Estados (status)

| Estado | Descripción |
|--------|-------------|
| `pending_owner_approval` | Usuario solicitó acceso, esperando aprobación del owner |
| `pending_player_acceptance` | Owner invitó al usuario, esperando aceptación |
| `active` | Jugador activo en la partida |
| `left` | Usuario abandonó voluntariamente |
| `kicked` | Usuario fue expulsado por el owner/admin |
| `banned` | Usuario fue baneado permanentemente |

## Métodos de Join (join_method)

| Método | Descripción |
|--------|-------------|
| `request` | Usuario solicitó unirse |
| `invitation` | Owner/admin invitó al usuario |
| `auto` | Se unió automáticamente (sin aprobación) |

## Roles y Permisos

### Roles Disponibles
- **Owner**: Creador de la partida, máximos permisos
- **Administrator**: Permisos administrativos delegados
- **Tester**: Acceso especial para pruebas

### Jerarquía de Permisos
```
Owner > Administrator > Tester > Player
```

## Ejemplos de Uso

### Crear una Relación Usuario-Partida

```php
// Usuario solicita unirse a una partida
$gameUser = GameUser::create([
    'game_id' => $game->id,
    'user_id' => $user->id,
    'status' => GameUser::STATUS_PENDING_OWNER,
    'join_method' => 'request'
]);

// Owner invita a un usuario
$gameUser = GameUser::create([
    'game_id' => $game->id,
    'user_id' => $invitedUser->id,
    'status' => GameUser::STATUS_PENDING_PLAYER,
    'join_method' => 'invitation',
    'actioned_by' => $owner->id
]);
```

### Consultas con Scopes

```php
// Obtener todos los jugadores activos de una partida
$activePlayers = GameUser::where('game_id', $gameId)
    ->active()
    ->with('user')
    ->get();

// Obtener owners de todas las partidas
$owners = GameUser::owners()->with(['user', 'game'])->get();

// Obtener solicitudes pendientes para un owner
$pendingRequests = GameUser::where('game_id', $gameId)
    ->where('status', GameUser::STATUS_PENDING_OWNER)
    ->with('user')
    ->get();
```

### Verificación de Roles

```php
$gameUser = GameUser::find(1);

// Verificar rol específico
if ($gameUser->hasRole('owner')) {
    // El usuario es owner de esta partida
}

// Verificar múltiples roles
if ($gameUser->hasAnyRole(['owner', 'administrator'])) {
    // El usuario tiene permisos administrativos
}

// Obtener todos los roles
$roles = $gameUser->getRoles(); // ['owner', 'tester']
```

### Gestión de Estados

```php
// Activar un usuario (cuando ambas partes aprueban)
$gameUser->update([
    'status' => GameUser::STATUS_ACTIVE,
    'joined_at' => now()
]);

// Expulsar un usuario
$gameUser->update([
    'status' => GameUser::STATUS_KICKED,
    'left_at' => now(),
    'actioned_by' => $admin->id,
    'reason' => 'Violación de reglas'
]);

// Usuario abandona voluntariamente
$gameUser->update([
    'status' => GameUser::STATUS_LEFT,
    'left_at' => now()
]);
```

### Consultas Avanzadas

```php
// Obtener historial completo de una partida
$gameHistory = GameUser::where('game_id', $gameId)
    ->with(['user', 'actionedBy'])
    ->orderBy('created_at')
    ->get();

// Verificar si un usuario puede unirse (no está baneado)
$canJoin = !GameUser::where('game_id', $gameId)
    ->where('user_id', $userId)
    ->where('status', GameUser::STATUS_BANNED)
    ->exists();

// Contar jugadores activos
$activeCount = GameUser::where('game_id', $gameId)
    ->active()
    ->count();
```

## Flujos de Trabajo

### Flujo 1: Usuario Solicita Acceso
1. Usuario accede con invitation_code
2. Se crea registro con `status = 'pending_owner_approval'`
3. Owner recibe notificación
4. Owner aprueba → `status = 'active'`

### Flujo 2: Owner Invita Usuario
1. Owner envía invitación
2. Se crea registro con `status = 'pending_player_acceptance'`
3. Usuario recibe notificación
4. Usuario acepta → `status = 'active'`

### Flujo 3: Auto-autorización
1. Usuario accede con invitation_code
2. Si `auto_authorize_players = true` en Game
3. Se crea directamente con `status = 'active'`

## Relaciones

```php
// Obtener la partida
$game = $gameUser->game;

// Obtener el usuario
$user = $gameUser->user;

// Obtener quién ejecutó la última acción
$actionedBy = $gameUser->actionedBy;
```

## Validaciones Recomendadas

- Un usuario solo puede ser owner de una partida
- No puede haber más usuarios activos que `max_users_per_instance`
- Un usuario baneado no puede volver a unirse
- Solo owners/admins pueden expulsar usuarios

## Validaciones de Unión a Partida

### Estados de Game vs allow_late_join

| Estado Game | allow_late_join = true | allow_late_join = false |
|-------------|------------------------|-------------------------|
| waiting     | ✅ Permitido          | ✅ Permitido           |
| running     | ✅ Permitido          | ❌ Bloqueado           |
| finished    | ❌ Bloqueado          | ❌ Bloqueado           |
| cancelled   | ❌ Bloqueado          | ❌ Bloqueado           |
