# Marco
Framework para backend de videojuegos en Laravel
## Tilesets
Los tiles de un tileset (visto como matriz) se referencian mediante dos coordenadas hexadecimales (entre 0 y F), en función del ancho y alto de cada tile. Es decir, un tileset de 12x10 elementos de mazmorra cuyos tiles son de 32 x 32 pixeles, tendría una imagen de 384x320 pixeles. Y cada tile sería referenciado con dos valores hexadecimales entre 0 y C para la columna y entre 0 y A para la fila; por ejemplo un tile podría referenciarce así: 3B
De igual forma, pueden haber múltiples tilesets para organizar distintos tipos de elementos del juego: por ejemplo, vegetación, agua, montañas, decoraciones e incluso UI.
Los diferentes tilesets tienen un número (también hexadecimal) para ser referenciados.
## Tilemaps
Los tilemaps definen las características globales de un mapa tales como:
- map-width y map-height, en unidades de tiles. Por ejemplo 1200 x 1200 tiles de ancho y alto.
## TileLayers
Cada layer se define como una zona rectangular contenida en el tilemap, es decir tiene coordenadas (x, y, layer-width, layer-height) con x > 0 ; y > 0; layer-width < map-width; layer-height < map-height.
Cada capa tiene un nombre, y un orden Z siendo 0, el nivel de terreno base, niveles > 0 estructuras y accidentes. y las capas con valores < 0, zonas subterraneas o subacuáticas.
Otros layers representan colisiones, o zonas de peligro, algunos son visibles o otros no. También hay layers para pathfinding.
Un tilelayer se define como una secuencia de tres valores hexadecimales, en donde el primer número hace referencia al tileset, y los otros dos son las coordenadas del tile específico. Así, un tilelayer de 3x3 podría ser:
000000000
0000AB0C1
0BC110200
Traducido significa:
000 es espacio en blanco
0AB sería el tile AB del tileset 0
0C1 sería el tile C1 del tileset 0
110 sería el tile 10 del tileset 1


# Pedido
## 1
Genera un tileset con tiles de 64x64 para elementos de terreno, bosque, rio, márgenes, mar y playa. No hace falta que generes la imagen, sólo describe qué deberían tener los tiles. Por ejemplo: de 00 a 09, playa; de 01 a 41 (zona rectangular) bosque. etc.
## 2
Con ese tileset, necesito que programes una clase con un método estático para generar terrenos procedurales que recibe:
- el ancho y alto de un mapa
- el tileset que generaste antes
- una o más semillas que indiquen la aleatoriedad, cuanta elevación tendría, cuántos rios y agua tendría.
- se deberían generar espacios más o menos planos para ubicar aldeas o ciudades y caminos o rutas entre ellas.
- También puentes.
