<?php

namespace App\Utils;

class ImageUtils
{
    private const DEFAULT_FONT_SIZE = 12;
    private const DEFAULT_TILE_WIDTH = 128;
    private const DEFAULT_TILE_HEIGHT = 128;
    private const DEFAULT_TILESET_ROWS = 3;
    private const DEFAULT_TILESET_COLUMNS = 3;
    private const DEFAULT_TEXT_COLOR = 'white';
    private const DEFAULT_BACKGROUND_COLOR = 'random';
    private const DEFAULT_SHAPE = 'rectangle';
    private const DEFAULT_RESOURCE_TYPE = 'image';

    public static function fakeImage(string $path, array $resource, int $creationTime = 0)
    {
        $type = $resource['type'] ?? self::DEFAULT_RESOURCE_TYPE;
        switch ($type) {
            case self::DEFAULT_RESOURCE_TYPE:
                self::fakeSimpleImage($path, $resource, $creationTime);
                break;
            case 'tileset':
                self::fakeTileset($path, $resource, $creationTime);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported resource type: $type");
        }
    }

    private static function fakeSimpleImage(string $path, array $resource, int $creationTime = 0)
    {
        $shape = $resource['shape'] ?? self::DEFAULT_SHAPE;
        $backgroundColor = $resource['color'] ?? ColorDef::getRandomColorName();
        $backgroundRGB = ColorDef::getColor($backgroundColor);
        $textColor = $resource['textColor'] ?? self::DEFAULT_TEXT_COLOR;
        $textRGB = ColorDef::getColor($textColor);
        $fontSize = $resource['fontSize'] ?? self::DEFAULT_FONT_SIZE;
        $fileName = pathinfo($path, PATHINFO_BASENAME);
        $width = $resource['width'];
        $height = $resource['height'];
        $text = $resource['text'] ?? $fileName;
        $resourceType = $resource['ext'];

        $image = self::createBaseImage($width, $height);
        self::fillShape($image, $shape, $backgroundRGB, $width, $height);
        self::addTextToImage($image, $text, $fontSize, $textRGB, $width, $height);

        self::saveImage($image, $path, $resourceType);
        if ($creationTime !== 0) {
            touch($path, $creationTime);
        }
        imagedestroy($image);
    }

    private static function createBaseImage(int $width, int $height)
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        return $image;
    }

    private static function fillShape($image, string $shape, array $backgroundRGB, int $width, int $height)
    {
        switch ($shape) {
            case self::DEFAULT_SHAPE:
                imagefill($image, 0, 0, imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]));
                break;
            case 'circle':
                self::fillCircle($image, $backgroundRGB, $width, $height);
                break;
            case 'triangle':
                self::fillTriangle($image, $backgroundRGB, $width, $height);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported shape: $shape");
        }
    }

    private static function fillCircle($image, array $backgroundRGB, int $width, int $height)
    {
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        $circleColor = imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]);
        imagefilledellipse($image, $width / 2, $height / 2, $width, $height, $circleColor);
    }

    private static function fillTriangle($image, array $backgroundRGB, int $width, int $height)
    {
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        $triangleColor = imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]);
        $trianglePoints = [
            $width / 2,
            0,
            0,
            $height,
            $width,
            $height
        ];
        imagefilledpolygon($image, $trianglePoints, 3, $triangleColor);
    }

    private static function addTextToImage($image, string $text, int $fontSize, array $textRGB, int $width, int $height)
    {
        $textColor = imagecolorallocate($image, $textRGB[0], $textRGB[1], $textRGB[2]);
        $fontPath = app_path('GameApps/Common/resources/fonts/arial.ttf');
        self::drawCenteredText($image, $text, $fontPath, $fontSize, $textColor, $width / 2, $height / 2);
    }

    private static function saveImage($image, string $path, string $resourceType)
    {
        switch ($resourceType) {
            case 'png':
                imagepng($image, $path);
                break;
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, $path);
                break;
            case 'gif':
                imagegif($image, $path);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported resource type: $resourceType");
        }
    }

    private static function fakeTileset(string $path, array $resource, int $creationTime = 0)
    {
        $textColor = $resource['textColor'] ?? self::DEFAULT_TEXT_COLOR;
        $textRGB = ColorDef::getColor($textColor);
        $fontSize = $resource['fontSize'] ?? self::DEFAULT_FONT_SIZE;
        $tileWidth = $resource['tileWidth'] ?? self::DEFAULT_TILE_WIDTH;
        $tileHeight = $resource['tileHeight'] ?? self::DEFAULT_TILE_HEIGHT;
        $tilesetRows = $resource['tilesetRows'] ?? self::DEFAULT_TILESET_ROWS;
        $tilesetColumns = $resource['tilesetCols'] ?? self::DEFAULT_TILESET_COLUMNS;
        $backgroundColor = $resource['color'] ?? ColorDef::getRandomColorName();
        $backgroundRGB = ColorDef::getColor($backgroundColor);

        $tilesetWidth = $tileWidth * $tilesetColumns;
        $tilesetHeight = $tileHeight * $tilesetRows;

        $image = imagecreatetruecolor($tilesetWidth, $tilesetHeight);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $backgroundColor = imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]);
        imagefill($image, 0, 0, $backgroundColor);

        for ($row = 0; $row < $tilesetRows; $row++) {
            for ($col = 0; $col < $tilesetColumns; $col++) {
                $x = $col * $tileWidth;
                $y = $row * $tileHeight;
                $tileColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
                imagefilledrectangle($image, $x, $y, $x + $tileWidth, $y + $tileHeight, $tileColor);
                $text = strtoupper(dechex($row)) . strtoupper(dechex($col));
                self::addTextToImage($image, $text, $fontSize, $textRGB, 2 * $x + $tileWidth, 2 * $y + $tileHeight);
            }
        }

        $resourceType = $resource['ext'];
        self::saveImage($image, $path, $resourceType);

        if ($creationTime !== 0) {
            touch($path, $creationTime);
        }
        imagedestroy($image);
    }

    private static function drawCenteredText($image, $text, $font, $size, $color, $x, $y)
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $textWidth = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x = $x - $textWidth / 2;
        $y = $y + $textHeight / 2;
        $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 0);
        imagettftext($image, $size, 0, $x + 2, $y + 2, $shadowColor, $font, $text);
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    }
}
