<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Task $task;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        // Создаем проект для пользователя
        $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
        
        // Создаем задачу в этом проекте
        $this->task = Task::factory()->create(['project_id' => $this->project->id]);
    }

    /** @test */
    public function test_user_can_create_comment()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/comments', [
            'task_id' => $this->task->id,
            'body' => 'This is a test comment',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.body', 'This is a test comment')
            ->assertJsonPath('data.user_id', $this->user->id);

        $this->assertDatabaseHas('comments', ['body' => 'This is a test comment']);
    }

    /** @test */
    public function test_user_can_view_comments_list()
    {
        Comment::factory()->count(3)->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/comments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function test_user_can_filter_comments_by_task()
    {
        // Создаем второй проект и задачу
        $project2 = Project::factory()->create(['owner_id' => $this->user->id]);
        $task2 = Task::factory()->create(['project_id' => $project2->id]);
        
        Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id
        ]);
        Comment::factory()->create([
            'task_id' => $task2->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/comments?task_id={$this->task->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.task_id', $this->task->id);
    }

    /** @test */
    public function test_user_can_update_own_comment()
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id, 
            'task_id' => $this->task->id
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/comments/{$comment->id}", [
            'body' => 'Updated comment body',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.body', 'Updated comment body');
    }

    /** @test */
    public function test_user_can_delete_own_comment()
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id, 
            'task_id' => $this->task->id
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Comment deleted successfully']);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /** @test */
    public function test_user_cannot_update_others_comment()
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $otherUser->id, 
            'task_id' => $this->task->id
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/comments/{$comment->id}", [
            'body' => 'Hacked comment',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_user_cannot_delete_others_comment()
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $otherUser->id, 
            'task_id' => $this->task->id
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}