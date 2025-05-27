# GameApp
Un objeto de gameApp, tiene atributos que definen características generales de una aplicación de juego.

Para determinar si una partida específica puede ser single o multiplayer existen estos dos atributos:
    - min_users_per_instance. Define la cantidad mínima de usuarios (mayor o igual a 1) requerida para generar una partida y que se pueda jugar.
    - max_users_per_instance. Establece la cantidad máxima de usuarios para poder jugar en una partida. Debe ser un valor mayor o igual a 1.

Respecto a la cantidad de partidas que un usuario (jugador) puede tener sin finalizar (que no hayan terminado en success o game over), existe el siguiente atributo:
    - max_instances_per_user. Este valor debe ser mayor o igual a 1.

    
Agrega funcionalidad al GameAppController, para que, cuando se ejecute el método play, retorne: 
- Una lista de partidas "abiertas", ordenadas por fecha, si el jugador puede crear más de una y continuar jugando.
- Una lista de jugadores de la partida, en caso de que se pueda jugar sólo una partida con más de un jugador.




En base a los modelos: #file:Game.php, #file:GameApp.php, #file:User.php y la tabla pivote #file:11_create_game_user_table.php. Agrega funcionalidad en el servicio #file:GameInstanceService.php para que, dado el usuario actual, un GameApp y un código de invitación (opcional) se tengan en cuenta los siguientes puntos y reglas:

1. Si no se especifica el código de invitación.
    1.1. Buscar las partidas abiertas (Game) del GameApp especificado.

1. Si se especifica el código de invitación, buscar la partida (Game) a la que corresponde.
    - Buscar el usuario actual entre los usuarios de la partida.
    Si no se encuentra el usuario actual entre los usuarios de la partida, agregarlo.


disparar una excepción si: no existe la partida, o ésta finalizó, o ésta corresponde a otro id de GameApp difrente al especificado, o el GameApp corresponde pero superó la cantidad máxima de usuarios
