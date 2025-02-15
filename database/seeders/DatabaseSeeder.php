<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        fake()->seed(10);
        $this->call(UsersSeeder::class);
    }
}
