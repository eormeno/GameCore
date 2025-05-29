<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameApp>
 */
class GameAppFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->text,
            'min_age' => 18,
            'max_instances_per_user' => 1,
            'min_users_per_instance' => 1,
            'max_users_per_instance' => 1,
            'allow_late_join' => false,
            'active' => true,
            'version' => '1.0.0',
        ];
    }
}
