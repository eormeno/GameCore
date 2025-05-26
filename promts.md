Context: Controlador de Juego
El $gameApp hallado, tiene dos atributos para determinar si una partida es multijugador o de jugador único, ellos son:
- min_users_per_instance. Este indica la cantidad mínima de usuarios requerida para generar una partida. Nunca puede ser menor que 1.
- max_users_per_instance. Indica la cantidad máxima de usuarios para poder jugar en una partida. Debe ser un valor mayor o igual a 1.
También tiene un atributo que indica cuántas partidas abiertas (que no hayan terminado en success o game over) puede crear un usuario:
- max_instances_per_user. Este valor debe ser mayor o igual a 1.
Agrega funcionalidad al GameAppController, para que, cuando se ejecute el método play, retorne: 
- Una lista de partidas "abiertas", ordenadas por fecha, si el jugador puede crear más de una y continuar jugando.
- Una lista de jugadores de la partida, en caso de que se pueda jugar sólo una partida con más de un jugador.
