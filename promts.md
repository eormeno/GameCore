# Requisito estructurado para funcionalidad de partidas en GameApp (Laravel Backend)

## Contexto y Modelos Involucrados
- Modelos: `GameApp`, `Game`, `User`
- Tabla pivote: `game_user`
- Archivos relevantes: 
  - `GameApp.php`
  - `Game.php`
  - `User.php`
  - `11_create_game_user_table.php`
  - `GameFactory.php`
  - `GameAppController.php`
  - `GameInstanceService.php`
  - `api.php`

## 1. Atributos y reglas de GameApp y Game

- `GameApp` define:  
  - min_users_per_instance (>=1)  
  - max_users_per_instance (>=1)  
  - max_instances_per_user (>=1)  
  - width, height (dimensiones de la pantalla del juego)  
  - resources_url (url de recursos)  

- `Game`:
  - invitation_code
  - game_app_id
  - game_object_id
  - elapsed
  - name
  - state (0: waiting, 1: running, 2: finished)
  - auto_authorize_players

- Tabla pivote `game_user`:
  - id
  - game_id
  - user_id
  - is_owner (es true si el que creó la instancia es el mismo jugador)
  - access_approved (true si el owner de la partida aprobó la solicitud)
  - invitation_approved (true si el jugador aceptó jugar en la partida)
  - timestamps (fecha y hora de creación y modificación)

---

## 2. Endpoint: `play` en GameAppController

- Recibe: 
  - usuario autenticado
  - id de GameApp
  - parámetro opcional invitation_code
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
  [estado] puede ser:
    - "game" cuando tras realizar todas las validaciones, la interfaz del jugador ya puede comenzar a renderizar la partida en sí.
    - "waiting". Para el caso de juegos multijugador, va mostrando la cantidad de jugadores requerida y la cantidad de jugadores que se van sumando. Y en caso de que el usuario que está recibiendo la respuesta sea el owner de la partida y que se haya llegado a la cantidad mínima de jugadores para iniciar el juego, enviar un atributo para que la UI pueda renderizar el botón "Start Game".
    - "error". Para retornar cualquier excepción.
    - "open_games". Con el listado de las partidas abiertas en caso de que la configuración del GameApp permita más de una partida. Y en caso de que se aún queden partidas disponibles, agregar un atributo para indicar a la UI que renderice el botón "New game".

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
  - El campo `state` será "waiting" para state==0 y "running" para state==1.
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

- Si se juega una partida específica: key `"game"`.
- Si hay partidas abiertas: key `"open_games"` (u opcionalmente `"waiting"` para multijugador con opción de iniciar).
- Siempre incluir width, height y resourcesUrl.
- En partidas multijugador, incluir `"showStartGameButton": true` solo si el owner puede iniciar la partida.

---
**Este requisito estructurado puede ser usado como prompt para implementar la funcionalidad en un entorno de desarrollo como Visual Studio Code.**
