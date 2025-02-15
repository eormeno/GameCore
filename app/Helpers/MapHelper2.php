<?php

namespace App\Helpers;

class MapHelper2
{

    public static function generateMap(int $width, int $height, int $traps): array
    {
        $tiles = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $tiles[] = [
                    'x' => $x,
                    'y' => $y,
                    'state' => null,
                    'entered_at' => null,
                    'has_trap' => false,
                    'has_flag' => false,
                    'marked_as_clue' => false,
                    'traps_around' => 0,
                ];
            }
        }
        self::fillTraps($traps, $width, $height, $tiles);
        return $tiles;
    }

    private static function fillTraps(int $count, int $width, int $height, array &$tiles): void
    {
        $randomPositions = self::generateUniqueRandomPositions($count, $width * $height);
        foreach ($randomPositions as $pos) {
            $x = $tiles[$pos]['x'];
            $y = $tiles[$pos]['y'];
            $tiles[$pos]['has_trap'] = true;
            self::incrementTrapsAround($tiles, $width, $height, $x, $y);
        }
    }

    private static function incrementTrapsAround(array &$tiles, int $width, int $height, int $x, int $y): void
    {
        $directions = [[-1, -1], [0, -1], [1, -1], [-1, 0], [1, 0], [-1, 1], [0, 1], [1, 1]];
        foreach ($directions as $dir) {
            $newX = $x + $dir[0];
            $newY = $y + $dir[1];
            if ($newX >= 0 && $newX < $width && $newY >= 0 && $newY < $height) {
                $tile = &$tiles[$newY * $width + $newX];
                $tile['traps_around']++;
            }
        }
    }

    private static function generateUniqueRandomPositions(int $count, int $mapSize): array
    {
        $positions = [];
        while (count($positions) < $count) {
            $position = rand(0, $mapSize - 1);
            if (!in_array($position, $positions)) {
                $positions[] = $position;
            }
        }
        return $positions;
    }
}
