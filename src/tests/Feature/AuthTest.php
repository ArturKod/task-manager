<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_only_owner_can_update_project()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($other)
            ->putJson("/api/v1/projects/{$project->id}", ['name' => 'Hacked'])
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson("/api/v1/projects/{$project->id}", ['name' => 'New Name'])
            ->assertStatus(200);
    }

    /** @test */
    public function test_only_owner_can_delete_project()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($other)
            ->deleteJson("/api/v1/projects/{$project->id}")
            ->assertStatus(403);

        $this->actingAs($owner)
            ->deleteJson("/api/v1/projects/{$project->id}")
            ->assertStatus(200);
    }

    /** @test */
    public function test_only_owner_can_update_task()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->actingAs($other)
            ->putJson("/api/v1/tasks/{$task->id}", ['title' => 'Hacked'])
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson("/api/v1/tasks/{$task->id}", ['title' => 'New Title'])
            ->assertStatus(200);
    }

    /** @test */
    public function test_only_owner_or_author_can_delete_comment()
    {
        $owner = User::factory()->create();
        $author = User::factory()->create();
        $other = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $author->id,
        ]);

        $this->actingAs($other)
            ->deleteJson("/api/v1/comments/{$comment->id}")
            ->assertStatus(403);

        $this->actingAs($author)
            ->deleteJson("/api/v1/comments/{$comment->id}")
            ->assertStatus(200);

        // Создадим новый комментарий, так как первый уже удалён
        $comment2 = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $author->id,
        ]);

        $this->actingAs($owner)
            ->deleteJson("/api/v1/comments/{$comment2->id}")
            ->assertStatus(200);
    }
}