<?php

namespace Database\Factories;

use Storage;
use Exception;
use App\Utils\ImageUtils;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameApp>
 */
class GameAppFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->text,
            'min_age' => 18,
            'max_instances_per_user' => 1,
            'min_users_per_instance' => 1,
            'max_users_per_instance' => 1,
            'active' => true,
            'version' => '1.0.0',
        ];
    }

    public function image(string $path, string $image): static
    {
        $image_full_path = "$path/$image";
        if (!file_exists($image_full_path)) {
            throw new Exception("Image [$image] not found in [$path] folder.");
        }
        $image = "images/$image";
        // if the image exists in the public folder, delete it
        if (!Storage::disk('public')->exists($image)) {
            // copy the image to the public folder of the app
            Storage::disk('public')->put($image, file_get_contents($image_full_path));
        }
        return $this->state(function (array $attributes) use ($image) {
            return [
                'image' => $image,
            ];
        });
    }

    // a factory with a fake image
    public function fakeImage(): static
    {
        return $this->state(function (array $attributes) {
            $threeLetterCode = $this->faker->unique()->regexify('[A-Z]{3}');
            $fakeImage = ImageUtils::saveImage(640, 480, "images/$threeLetterCode-fake.jpg");
            return [
                'prefix' => $threeLetterCode,
                'image' => $fakeImage,
            ];
        });
    }
}
