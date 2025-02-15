<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Storage;

class ImageUtils
{
    public static function downloadImage(int $width, int $height): ?array
    {
        try {
            $response = Http::get("https://picsum.photos/{$width}/{$height}");
            return self::base64Images($response->body());
        } catch (\Exception $e) {
            // Log error by console
            echo $e->getMessage();
            return null;
        }
    }

    public static function saveImage(int $width, int $height, string $filename): string
    {
        try {
            if (Storage::disk('public')->exists($filename)) {
                return $filename;
            }
            $response = Http::get("https://picsum.photos/{$width}/{$height}");
            Storage::disk('public')->put($filename, $response->body());
            return $filename;
        } catch (\Exception $e) {
            // Log error by console
            echo $e->getMessage();
            return null;
        }
    }

    public static function base64Images($source): array
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($source);
        $originalBase64 = base64_encode($image->toJpeg(90));
        $thumbnail = $image->resize(48, 48);
        $thumbnailBase64 = base64_encode($thumbnail->toJpeg(90));
        return [
            'original' => $originalBase64,
            'thumbnail' => $thumbnailBase64
        ];
    }

}
