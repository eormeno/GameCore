<?php

namespace App\Utils;

class ColorDef
{
    public const BLUE = '#0000ff';
    public const PURPLE = '#800080';
    public const CYAN = '#00ffff';
    public const MAGENTA = '#ff00ff';
    public const ORANGE = '#ffa500';
    public const LIME = '#00ff00';
    public const PINK = '#ffc0cb';
    public const TEAL = '#008080';
    public const LAVENDER = '#e6e6fa';
    public const BROWN = '#a52a2a';
    public const BEIGE = '#f5f5dc';
    public const MAROON = '#800000';
    public const MINT = '#3eb489';
    public const OLIVE = '#808000';
    public const CORAL = '#ff7f50';
    public const NAVY = '#000080';
    public const GREY = '#808080';
    public const WHITE = '#ffffff';
    public const BLACK = '#000000';
    public const DARK_BLUE = '#0000aa';
    public const DARK_GREEN = '#00aa00';
    public const DARK_AQUA = '#00aaaa';
    public const DARK_RED = '#aa0000';
    public const DARK_PURPLE = '#aa00aa';
    public const GOLD = '#ffaa00';
    public const GRAY = '#aaaaaa';
    public const DARK_GRAY = '#555555';
    public const GREEN = '#55ff55';
    public const LIGHT_PURPLE = '#ff55ff';
    public const RED = '#ff5555';
    public const YELLOW = '#ffff55';

    public static function getColor(string $color): array
    {
        $color = strtoupper($color);
        $value = constant("self::$color");
        return self::getRGB($value);
    }

    private static function getRGB(string $value): array
    {
        $r = hexdec(substr($value, 1, 2));
        $g = hexdec(substr($value, 3, 2));
        $b = hexdec(substr($value, 5, 2));
        return [$r, $g, $b];
    }

    public static function getRandomColorName(): string
    {
        $colorConstants = (new \ReflectionClass(self::class))->getConstants();
        $colors = array_keys($colorConstants);
        $randColor = strtolower($colors[array_rand($colors)]);
        return $randColor;
    }
}
