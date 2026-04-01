<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['new', 'in_progress', 'blocked', 'done', 'cancelled']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'critical']),
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
        ];
    }
}