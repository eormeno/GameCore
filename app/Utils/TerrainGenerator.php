<?php

// Archivo: app/Game/TerrainGenerator.php

namespace App\Utils;

class TerrainGenerator
{
    /**
     * Genera un mapa procedimental.
     *
     * @param int   $mapWidth  Ancho del mapa en número de tiles.
     * @param int   $mapHeight Alto del mapa en número de tiles.
     * @param array $tileset   Estructura del tileset definido.
     * @param array $seeds     Semillas para definir la aleatoriedad, elevación, ríos y cuerpos de agua.
     *
     * @return array Mapa generado en forma de matriz de tiles.
     */
    public static function generate(int $mapWidth, int $mapHeight, array $tileset, array $seeds = []): array
    {
        // Inicializar la semilla para asegurar reproducibilidad.
        if (!empty($seeds)) {
            $seedString = implode('-', $seeds);
            mt_srand(crc32($seedString));
        } else {
            mt_srand();
        }

        // Inicialización del mapa con la capa base de terreno.
        $map = [];
        for ($y = 0; $y < $mapHeight; $y++) {
            $map[$y] = [];
            for ($x = 0; $x < $mapWidth; $x++) {
                // Se asigna un tile de "terreno" aleatorio del rango 00-0F.
                $map[$y][$x] = self::getTerrainTile($tileset);
            }
        }

        // --- Generación de elevación, ríos y cuerpos de agua ---
        // Se define el número de ríos en función de las semillas:
        $numRivers = self::determineNumberOfRivers($seeds);

        for ($i = 0; $i < $numRivers; $i++) {
            // Se escoge un punto de inicio aleatorio para el río.
            $startX = mt_rand(0, $mapWidth - 1);
            $startY = mt_rand(0, $mapHeight - 1);

            // Se simula un cauce recto horizontal a partir del punto de inicio.
            for ($x = $startX; $x < $mapWidth; $x++) {
                $map[$startY][$x] = self::getRiverTile($tileset);
            }
        }

        // --- Generación de rutas y puentes ---
        // Ejemplo: Se crea un camino horizontal a lo largo de la mitad del mapa.
        $roadY = intval($mapHeight / 2);
        for ($x = 0; $x < $mapWidth; $x++) {
            // Si se cruza un río, se coloca un puente.
            if ($map[$roadY][$x] === self::getRiverTile($tileset)) {
                $map[$roadY][$x] = self::getBridgeTile($tileset);
            } else {
                // Se asigna un tile de camino.
                $map[$roadY][$x] = self::getRoadTile($tileset);
            }
        }

        // Se pueden agregar reglas adicionales para zonas de elevación, áreas planas o la interconexión de aldeas.
        return $map;
    }

    /**
     * Retorna un tile aleatorio de la categoría "terreno" (rango 00-0F).
     *
     * @param array $tileset Estructura del tileset.
     * @return string Código del tile.
     */
    protected static function getTerrainTile(array $tileset): string
    {
        $tileIndex = mt_rand(0x00, 0x0F);
        return sprintf("%02X", $tileIndex);
    }

    /**
     * Retorna un tile aleatorio de la categoría "río" (rango 30-3F).
     *
     * @param array $tileset Estructura del tileset.
     * @return string Código del tile.
     */
    protected static function getRiverTile(array $tileset): string
    {
        $tileIndex = mt_rand(0x30, 0x3F);
        return sprintf("%02X", $tileIndex);
    }

    /**
     * Retorna un tile representativo de un puente.
     *
     * @param array $tileset Estructura del tileset.
     * @return string Código del tile.
     */
    protected static function getBridgeTile(array $tileset): string
    {
        // Se asume que el tile de puente se define en la posición "70" del tileset.
        return "70";
    }

    /**
     * Retorna un tile representativo de una ruta o camino.
     *
     * @param array $tileset Estructura del tileset.
     * @return string Código del tile.
     */
    protected static function getRoadTile(array $tileset): string
    {
        // Se asume que el tile de camino se define en la posición "80" del tileset.
        return "80";
    }

    /**
     * Determina el número de ríos en el mapa basándose en las semillas.
     *
     * @param array $seeds Semillas utilizadas para la generación.
     * @return int Número de ríos.
     */
    protected static function determineNumberOfRivers(array $seeds): int
    {
        // Ejemplo: se define un número aleatorio entre 1 y 3.
        return mt_rand(1, 3);
    }
}
