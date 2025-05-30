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

| Estado | Constante | Descripción |
|--------|-----------|-------------|
| `pending_owner_approval` | `STATUS_PENDING_OWNER` | Usuario solicitó acceso, esperando aprobación del owner |
| `pending_player_acceptance` | `STATUS_PENDING_PLAYER` | Owner invitó al usuario, esperando aceptación |
| `active` | `STATUS_ACTIVE` | Jugador activo en la partida |
| `left` | `STATUS_LEFT` | Usuario abandonó voluntariamente |
| `kicked` | `STATUS_KICKED` | Usuario fue expulsado por el owner/admin |
| `banned` | `STATUS_BANNED` | Usuario fue baneado permanentemente |

## Métodos de Join (join_method)

| Método | Constante | Descripción |
|--------|-----------|-------------|
| `request` | `JOIN_REQUEST` | Usuario solicitó unirse |
| `invitation` | `JOIN_INVITATION` | Owner/admin invitó al usuario |
| `auto` | `JOIN_AUTO` | Se unió automáticamente (sin aprobación) |

## Roles y Permisos

### Roles Disponibles
- **Owner**: Creador de la partida, máximos permisos
- **Administrator**: Permisos administrativos delegados
- **Tester**: Acceso especial para pruebas

### Jerarquía de Permisos
```
Owner > Administrator > Tester > Player
```

## Constantes del Modelo

### Estados de Participación
```php
const STATUS_PENDING_OWNER = 'pending_owner_approval';
const STATUS_PENDING_PLAYER = 'pending_player_acceptance';
const STATUS_ACTIVE = 'active';
const STATUS_LEFT = 'left';
const STATUS_KICKED = 'kicked';
const STATUS_BANNED = 'banned';
```

### Métodos de Unión
```php
const JOIN_REQUEST = 'request';
const JOIN_INVITATION = 'invitation';
const JOIN_AUTO = 'auto';
```

## Configuración del Modelo

### Campos Fillable
```php
protected $fillable = [
    'game_id', 'user_id', 'is_owner', 'is_administrator', 'is_tester',
    'status', 'join_method', 'actioned_by', 'reason',
    'joined_at', 'left_at'
];
```

### Casts
```php
protected $casts = [
    'is_owner' => 'boolean',
    'is_administrator' => 'boolean',
    'is_tester' => 'boolean',
    'joined_at' => 'datetime',
    'left_at' => 'datetime',
];
```

## Ejemplos de Uso

### Crear una Relación Usuario-Partida

```php
// Usuario solicita unirse a una partida
$gameUser = GameUser::create([
    'game_id' => $game->id,
    'user_id' => $user->id,
    'status' => GameUser::STATUS_PENDING_OWNER,
    'join_method' => GameUser::JOIN_REQUEST
]);

// Owner invita a un usuario
$gameUser = GameUser::create([
    'game_id' => $game->id,
    'user_id' => $invitedUser->id,
    'status' => GameUser::STATUS_PENDING_PLAYER,
    'join_method' => GameUser::JOIN_INVITATION,
    'actioned_by' => $owner->id
]);

// Unión automática (single player o auto-authorize)
$gameUser = GameUser::create([
    'game_id' => $game->id,
    'user_id' => $user->id,
    'is_owner' => true,
    'status' => GameUser::STATUS_ACTIVE,
    'join_method' => GameUser::JOIN_AUTO,
    'joined_at' => now()
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

// Obtener administradores activos
$admins = GameUser::administrators()->active()->get();

// Obtener solicitudes pendientes para un owner
$pendingRequests = GameUser::where('game_id', $gameId)
    ->where('status', GameUser::STATUS_PENDING_OWNER)
    ->with('user')
    ->get();

// Obtener jugadores pendientes de aceptar invitación
$pendingInvitations = GameUser::where('user_id', $userId)
    ->where('status', GameUser::STATUS_PENDING_PLAYER)
    ->with('game')
    ->get();
```

### Verificación de Roles

```php
$gameUser = GameUser::find(1);

// Verificar rol específico
if ($gameUser->hasRole('owner')) {
    // El usuario es owner de esta partida
}

if ($gameUser->hasRole('administrator')) {
    // El usuario tiene permisos administrativos
}

// Verificar múltiples roles
if ($gameUser->hasAnyRole(['owner', 'administrator'])) {
    // El usuario tiene permisos administrativos
}

// Obtener todos los roles
$roles = $gameUser->getRoles(); // ['owner', 'tester']
```

### Verificación de Estados

```php
$gameUser = GameUser::find(1);

// Verificar si está activo
if ($gameUser->isActive()) {
    // Usuario puede participar en la partida
}

// Verificar si está pendiente
if ($gameUser->isPending()) {
    // Usuario esperando aprobación/aceptación
}

// Verificar estado específico
if ($gameUser->status === GameUser::STATUS_BANNED) {
    // Usuario baneado
}
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

// Banear un usuario
$gameUser->update([
    'status' => GameUser::STATUS_BANNED,
    'left_at' => now(),
    'actioned_by' => $admin->id,
    'reason' => 'Comportamiento tóxico repetido'
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

// Obtener partidas donde el usuario es owner
$ownedGames = GameUser::where('user_id', $userId)
    ->owners()
    ->active()
    ->with('game')
    ->get();

// Buscar usuarios por rol en una partida específica
$gameAdmins = GameUser::where('game_id', $gameId)
    ->administrators()
    ->active()
    ->with('user')
    ->get();
```

## Flujos de Trabajo

### Flujo 1: Usuario Solicita Acceso
1. Usuario accede con invitation_code
2. Se crea registro con `status = STATUS_PENDING_OWNER`, `join_method = JOIN_REQUEST`
3. Owner recibe notificación
4. Owner aprueba → `status = STATUS_ACTIVE`, `joined_at = now()`

### Flujo 2: Owner Invita Usuario
1. Owner envía invitación
2. Se crea registro con `status = STATUS_PENDING_PLAYER`, `join_method = JOIN_INVITATION`
3. Usuario recibe notificación
4. Usuario acepta → `status = STATUS_ACTIVE`, `joined_at = now()`

### Flujo 3: Auto-autorización
1. Usuario accede con invitation_code
2. Si `auto_authorize_players = true` en Game
3. Se crea directamente con `status = STATUS_ACTIVE`, `join_method = JOIN_AUTO`

### Flujo 4: Single Player
1. Usuario crea nueva partida
2. Se asigna automáticamente como owner
3. Estado directo `STATUS_ACTIVE` con `JOIN_AUTO`

## Relaciones

```php
// Obtener la partida
$game = $gameUser->game;

// Obtener el usuario
$user = $gameUser->user;

// Obtener quién ejecutó la última acción
$actionedBy = $gameUser->actionedBy;

// Relaciones inversas desde Game
$game = Game::with(['gameUsers' => function($query) {
    $query->active()->with('user');
}])->find($gameId);

// Relaciones inversas desde User
$user = User::with(['gameUsers' => function($query) {
    $query->active()->with('game');
}])->find($userId);
```

## Validaciones Recomendadas

### A nivel de aplicación:
- Un usuario solo puede ser owner de una partida por GameApp
- No puede haber más usuarios activos que `max_players_per_instance`
- Un usuario baneado no puede volver a unirse a la misma partida
- Solo owners/admins pueden expulsar usuarios
- Solo owners pueden asignar roles de administrator
- Un usuario no puede tener múltiples registros activos en la misma partida

### A nivel de base de datos:
```sql
-- Índice único para evitar duplicados
UNIQUE KEY unique_active_user_game (game_id, user_id, status) 
WHERE status = 'active';

-- Índices para consultas frecuentes
INDEX idx_game_status (game_id, status);
INDEX idx_user_status (user_id, status);
INDEX idx_game_owner (game_id, is_owner);
```

## Eventos del Modelo

```php
// En el modelo GameUser
protected static function booted()
{
    // Cuando se activa un usuario
    static::updated(function ($gameUser) {
        if ($gameUser->wasChanged('status') && $gameUser->status === self::STATUS_ACTIVE) {
            // Disparar evento de usuario activado
            event(new UserJoinedGame($gameUser));
        }
    });
    
    // Cuando se expulsa un usuario
    static::updated(function ($gameUser) {
        if ($gameUser->wasChanged('status') && 
            in_array($gameUser->status, [self::STATUS_KICKED, self::STATUS_BANNED])) {
            // Disparar evento de usuario expulsado
            event(new UserRemovedFromGame($gameUser));
        }
    });
}
```

## Testing

### Factory Example
```php
// database/factories/GameUserFactory.php
public function definition()
{
    return [
        'game_id' => Game::factory(),
        'user_id' => User::factory(),
        'is_owner' => false,
        'is_administrator' => false,
        'is_tester' => false,
        'status' => GameUser::STATUS_ACTIVE,
        'join_method' => GameUser::JOIN_REQUEST,
        'joined_at' => now(),
    ];
}

public function owner()
{
    return $this->state([
        'is_owner' => true,
        'join_method' => GameUser::JOIN_AUTO,
    ]);
}

public function pending()
{
    return $this->state([
        'status' => GameUser::STATUS_PENDING_OWNER,
        'joined_at' => null,
    ]);
}
```

### Test Examples
```php
public function test_user_can_be_activated()
{
    $gameUser = GameUser::factory()->pending()->create();
    
    $gameUser->update([
        'status' => GameUser::STATUS_ACTIVE,
        'joined_at' => now()
    ]);
    
    $this->assertTrue($gameUser->isActive());
    $this->assertNotNull($gameUser->joined_at);
}

public function test_owner_has_owner_role()
{
    $gameUser = GameUser::factory()->owner()->create();
    
    $this->assertTrue($gameUser->hasRole('owner'));
    $this->assertContains('owner', $gameUser->getRoles());
}
```
