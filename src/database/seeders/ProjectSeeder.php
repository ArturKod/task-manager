<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаём тестового пользователя, если его нет
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Создаём 5 проектов для этого пользователя
        $projects = Project::factory()
            ->count(5)
            ->for($user, 'owner')
            ->has(Task::factory()->count(3), 'tasks')
            ->create();

        foreach ($projects as $project) {
            foreach ($project->tasks as $task) {
                Comment::factory()->count(2)->create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
