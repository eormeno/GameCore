<?php

namespace App\Utils;

class ImageUtils
{
    public static function fakeImage(string $path, array $resource)
    {
        $shape = $resource['shape'] ?? 'rectangle';
        $backgroundColor = $resource['color'] ?? ColorDef::getRandomColorName();
        $backgroundRGB = ColorDef::getColor($backgroundColor);

        $textColor = $resource['textColor'] ?? 'white';
        $textRGB = ColorDef::getColor($textColor);
        $fontSize = $resource['fontSize'] ?? 12;

        $fileName = pathinfo($path, PATHINFO_BASENAME);
        $width = $resource['width'];
        $height = $resource['height'];
        $text = $resource['text'] ?? $fileName;
        $resourceType = $resource['ext'];
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        if ($shape === 'rectangle') {
            imagefill($image, 0, 0, imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]));
        }
        if ($shape === 'circle') {
            // paint the background as transparent
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
            // paint the ellipse
            $circleColor = imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]);
            imagefilledellipse($image, $width / 2, $height / 2, $width, $height, $circleColor);
        }
        if ($shape === 'triangle') {
            // paint the background as transparent
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
            $triangleColor = imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]);
            $trianglePoints = [
                $width / 2, 0,
                0, $height,
                $width, $height
            ];
            imagefilledpolygon($image, $trianglePoints, 3, $triangleColor);
        }

        // imagefill($image, 0, 0, imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]));
        $textColor = imagecolorallocate($image, $textRGB[0], $textRGB[1], $textRGB[2]);


        $fontPath = app_path('GameApps/Common/resources/fonts/arial.ttf');
        self::drawCenteredText($image, $text, $fontPath, $fontSize, $textColor, $width / 2, $height / 2);

        if ($resourceType === 'png') {
            imagepng($image, $path);
        }
        if ($resourceType === 'jpg' || $resourceType === 'jpeg') {
            imagejpeg($image, $path);
        }
        if ($resourceType === 'gif') {
            imagegif($image, $path);
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
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    }

}
