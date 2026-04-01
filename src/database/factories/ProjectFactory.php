<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' ' . $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'owner_id' => User::factory(),
            'status' => $this->faker->randomElement(['active', 'archived']),
        ];
    }
}