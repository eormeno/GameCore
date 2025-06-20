Requisitos para funcionalidad de partidas en GameCore

## Contexto y Modelos Involucrados

- Modelos:`GameApp`,`Game`,`User`
- Modelo pivote:`GameUser`
- Archivos relevantes:
  - [`app/Models/GameApp.php`](app/Models/GameApp.php)
  - [`app/Models/Game.php`](app/Models/Game.php)
  - [`app/Models/User.php`](app/Models/User.php)
  - [`app/Models/GameUser.php`](app/Models/GameUser.php)
  - [`database/factories/GameFactory.php`](database/factories/GameFactory.php)
  - [`app/Http/Controllers/GameAppController.php`](app/Http/Controllers/GameAppController.php)
  - [`app/Services/GameInstanceService.php`](app/Services/GameInstanceService.php)
  - [`routes/api.php`](routes/api.php)

## 1. Atributos y reglas de GameApp, Game y GameUser

- [`GameApp`](app/Models/GameApp.php) define:

  - `min_users_per_instance` (integer >= 1). Define la cantidad mínima de jugadores para iniciar una partida.
  - `max_users_per_instance` (integer >= 1). Define la cantidad máxima de jugadores por partida. Cuando el valor es 1, las partidas son de un solo jugador, y cuando es mayor a 1, son multijugador.
  - `max_instances_per_user` (integer >= 1). Define la cantidad máxima de partidas que un usuario puede tener abiertas al mismo tiempo. Aquí el concepto es equivalente al de "partidas guardadas" en otros juegos. Este valor es independiente de si la partida es de un solo jugador o multijugador.
  - `width` (integer > 1),`height` (integer > 1). Definen el ancho y alto de la interfaz en donde se renderizá el juego.
  - `allow_late_join` (boolean). Indica si se permite que nuevos jugadores se unan a una partida ya iniciada.
- [`Game`](app/Models/Game.php):

  - `invitation_code` (string, nullable). Código de invitación para unirse a la partida. Si es null, la partida es abierta.
  - `game_app_id` (foreign key). Referencia al GameApp asociado.
  - `game_object_id` (string, nullable). Identificador del objeto de juego raíz asociado.
  - `elapsed` (integer, nullable). Tiempo transcurrido en la partida desde su inicio.
  - `name` (string, nullable). Nombre de la partida. Si es null, se generará un nombre por defecto.
  - `state` (enumeration: 0=waiting, 1=running, 2=finished, 3=cancelled). Estado de la partida.
  - `auto_authorize_players` (boolean). Indica si los jugadores pueden unirse automáticamente sin aprobación del owner.
- Pivote [`GameUser`](app/Models/GameUser.php):

  - Ver[`GameUser.md`](app/Models/GameUser.md) para detalles de atributos, relaciones, validaciones y lógica de negocio.

---

## 2. Endpoint: [`play` en GameAppController](app/Http/Controllers/GameAppController.php#play)

- Recibe:
  - `gameAppId` con el identificador de la aplicación de juego.
  - parámetro opcional`invitation_code`
  - Inyección al servicio`GameInstanceService` para manejar la lógica de negocio.
  - El usuario actual (autenticado) a través de`auth->user()`.
- Retorna JSON con el estado del juego o de una partida, el cual tiene la siguiente estructura genérica:

```json
{
	"[estado]": {
		"width": "...",
		"height": "...",
		"resourcesUrl": "...",
	}
}
```

`[estado]` puede ser:

- `"game"` cuando tras realizar todas las validaciones, la interfaz del jugador ya puede comenzar a renderizar la partida en sí.
- `"waiting"`. Para el caso de juegos multijugador, va mostrando la cantidad de jugadores requerida y la cantidad de jugadores que se van sumando. Y en caso de que el usuario que está recibiendo la respuesta sea el owner de la partida y que se haya llegado a la cantidad mínima de jugadores para iniciar el juego, se agrega el atributo`start_game_enabled` con el valor`true` para que la UI pueda renderizar el botón "Start Game".
- `"error"`. Para retornar cualquier excepción.
- `"open_games"`. Con el listado de las partidas abiertas en caso de que la configuración del GameApp permita más de una partida. Y en caso de que se aún queden partidas disponibles, agregar un atributo para indicar a la UI que renderice el botón "New game".
- `resourcesUrl` es la URL donde se pueden obtener los recursos de la apliación de juego, como imágenes, sonidos, etc.
- `width` y`height` son las dimensiones de la interfaz del juego.

### 2.1 Si se provee invitation_code:

- Buscar partida (Game) correspondiente al código.
- Lanzar excepción si:
  - No existe partida.
  - Partida finalizada (state==2).
  - game_app_id no corresponde.
- Buscar el usuario en la partida.
  - Si el usuario es el owner, retornar estado
  - Excepción si: Partida llena (usuarios >= max_users_per_instance).
- Retornar estructura JSON:
  ```json
  {
	"game": {
  	"title": "Adventure Quest",
  	"eventUrl": "http://localhost:8000/api/event/123",
  	"resourcesUrl": "http://localhost:8000/api/res/45",
  	"width": 800,
  	"height": 600,
  	"invitationCode": "ABC12345",
  	"maxInstancesPerUser": 3,
  	"minUsersPerInstance": 1,
  	"maxUsersPerInstance": 4
	}
  }
  ```

### 2.2 Si NO se provee invitation_code:

- Buscar partidas abiertas (state==0 o 1) asociadas al GameApp.
- Si no hay partidas abiertas, lanzar excepción.
- Verificar que el usuario no haya superado el máximo de partidas abiertas.
- Retornar estructura JSON con key `open_games`:

  ```json
  {
	"open_games": [
  	{
  	  "id": 23,
  	  "name": "Partida 1",
  	  "invitationCode": "CODE1",
  	  "state": "waiting",
  	  "users": [
  		{ "id": 1, "name": "Alice", "is_owner": true, "access_approved": true, "invitation_approved": true },
  		{ "id": 2, "name": "Bob", "is_owner": false, "access_approved": true, "invitation_approved": true }
  	  ],
  	  "createdAt": "2025-05-29T12:00:00Z"
  	},
  	...
	],
	"resourcesUrl": "http://localhost:8000/api/res/45",
	"width": 800,
	"height": 600
  }
  ```

  - Las partidas deben ir ordenadas por fecha de creación (más reciente primero).
  - El campo`state` será "waiting" para state==0 y "running" para state==1.
  - Incluir la lista de usuarios con información relevante.

### 2.3 Si la partida es multijugador y el usuario actual es el owner:

- Si la cantidad de jugadores agregados >= min_users_per_instance, retornar en el JSON un atributo adicional:
  ```json
  {
	...,
	"showStartGameButton": true
  }
  ```
- Este atributo permite a la UI mostrar el botón "Start Game" solo cuando el owner puede iniciar la partida.

---

## 3. Validaciones y excepciones

- GameAppNotFoundException
- GameNotFoundException
- GameAlreadyFinishedException
- GameAppMismatchException
- MaxUsersPerInstanceReachedException
- MaxInstancesPerUserReachedException
- InvitationCodeNotFoundException

---

## 4. Consideraciones técnicas

- TODA la lógica va en GameInstanceService, el controlador solo delega.
- Usar Eloquent, relaciones y métodos del modelo, evitar SQL crudo.
- El código debe ser limpio, legible, fácil de mantener y coherente con el resto del proyecto.
- Usar recursos (Resource) de Laravel para la estructura JSON.
- El endpoint debe aceptar el parámetro opcional invitation_code.
- El código debe estar listo para pruebas unitarias y de integración.

---

## 5. Ejemplo de respuesta JSON para partidas multijugador esperando inicio

```json
{
  "waiting": [
	{
	  "id": 23,
	  "name": "Partida Multijugador",
	  "invitationCode": "CODE2",
	  "state": "waiting",
	  "users": [
		{ "id": 1, "name": "Alice", "is_owner": true },
		{ "id": 2, "name": "Bob", "is_owner": false }
	  ],
	  "createdAt": "2025-05-29T12:00:00Z"
	}
  ],
  "resourcesUrl": "http://localhost:8000/api/res/45",
  "width": 800,
  "height": 600,
  "showStartGameButton": true
}
```

---

## 6. Resumen de estructura de respuesta

- Si se juega una partida específica: key`"game"`.
- Si hay partidas abiertas: key`"open_games"` (u opcionalmente`"waiting"` para multijugador con opción de iniciar).
- Siempre incluir width, height y resourcesUrl.
- En partidas multijugador, incluir`"showStartGameButton": true` solo si el owner puede iniciar la partida.

---

**Este requisito estructurado puede ser usado como prompt para implementar la funcionalidad en un entorno de desarrollo como Visual Studio Code.**

### Definiciones

**Partida**: De ahora en adelante, el término "Partida" se refiera a una instancia de `Game`, asociada a un objeto `GameApp` que tiene un conjunto de jugadores (instancias del modelo pivote `GameUser`), un conjunto de servicios con información de la misma y un estado que puede ser `WAITING`, `RUNNING`, `FINISHED` o `CANCELLED`. Puede ser de un solo jugador o multijugador. En otros contextos, una partida puede ser referida como un "save".

### Requisitos para el endpoint `play` de `GameAppController`

Toda la lógica debe pasar por el método del `GameAppController->play()`, el cual, tal como mencionas anteriormente, recibe `$gameAppId` + opcional `$invitationCode`. Este método requiere tener definidas las siguientes variables:

- `$currenUser`. El usuario actualmente autenticado.
- `$gameApp`. Obtenido a partir de`$gameAppId`.
- `$gameService`. Inyección del servicio`GameInstanceService`.
- `$isGameDefined`. Resulta de la operación booleana`$gameOfInvitation != null.`
- `$isGameUndefined`. Resulta de la operación booleana`$gameOfInvitation == null.`
- `$isSinglePlayer`. Resulta de la operación booleana`$gameApp->max_users_per_instance == 1.`
- `$isMultiPlayer`. Resulta de la operación booleana`$gameApp->max_users_per_instance > 1`.
- `$isUniqueGame`. Resultado de la operación booleana`$gameApp->max_instances_per_user == 1`.
- `$isMultiGame`. Resultado de la operaciòn boleana`$gameApp->max_instances_per_user > 1`.
- `$gameOfInvitation`. Objeto`Game `resultante de buscar`$invitationCode`. Será`null` si`$invitationCode` es`null`.

#### Validaciones

- Si `$gameAppId` no existe o no está `active`, disparar `GameApplicationNotFoundException`.
- Si `$invitationCode` no se encuentra, disparar `GameNotFoundException`

#### Regla de negocio 1

**Variables evaluadas en `true`:**
- `$isSinglePlayer`
- `$isUniqueGame`
- `$isGameUndefined`

**Acción:**
- Asignar a `$userGame` al resultado de buscar en `GameUser` el `Game` asociado a `$currenUser`, o `null` si no existe.
- Si `$userGame == null`. 
	- Asignar a `$userGame` una nueva instancia de `GameUser` para `$gameApp` y `$currentUser`.
	- Setear `$userGame`



 Ese es el caso que actualmente está implementado con`$gameService->getOrCreateUserGame($currentUser, $gameApp)`. Dado que será una única partida para el jugador autenticado, al crear la partida debe estar en estado`RUNNING`.

#### Regla 2

Si `$currentUser` está en la lista `$gameOfInvitation->players`,

Así, se pueden dar los siguientes casos según se especifique o no el `$invitationCode`:

1. **Sin invitationCode**
   * Si`$gameApp->max_users_per_instance == 1`. Es el caso single player.
	 - Si`$gameApp->max_instances_per_user == 1`. El jugador puede tener una única partida, entonces:
	   - Si`$invitationCode == null`:
	   - Buscar o crear si no existe un`Game` asociado al`User`. Ese es el caso que actualmente está implementado con`$gameService->getOrCreateUserGame($currentUser, $gameApp)`. Dado que será una única partida para el jugador autenticado, al crear la partida debe estar en estado`RUNNING`.
	   - Retornar el estado "game" es decir, listo para jugar en esa única instancia para single player (tal como está implementado actualmente).
	   - En este caso, la partida debería estar en estado`RUNNING`. Si estuviera en otro estado, debe disparar la excepción`GameInvalidStateException` indicando el estado.
   * Si`$gameApp->max_instances_per_user > 1` (pueden haber múltiples instanciasmás de una partida pueden haber)
2. **Con invitationCode**
