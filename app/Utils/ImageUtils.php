<?php

namespace App\Utils;

class ImageUtils
{
    public static function fakeImage(string $path, array $resource)
    {
        $backgroundColor = $resource['color'] ?? ColorDef::getRandomColorName();
        $backgroundRGB = ColorDef::getColor($backgroundColor);

        $textColor = $resource['textColor'] ?? 'white';
        $textRGB = ColorDef::getColor($textColor);

        $fileName = pathinfo($path, PATHINFO_BASENAME);
        $width = $resource['width'];
        $height = $resource['height'];
        $text = $resource['text'] ?? $fileName;
        $resourceType = $resource['ext'];
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]));
        $textColor = imagecolorallocate($image, $textRGB[0], $textRGB[1], $textRGB[2]);


        $fontPath = app_path('GameApps/Common/resources/fonts/arial.ttf');
        self::drawCenteredText($image, $text, $fontPath, 12, $textColor, $width / 2, $height / 2);

        if ($resourceType === 'png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
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
