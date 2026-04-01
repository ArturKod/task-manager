<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
    }

    /** @test */
    public function test_user_can_create_task()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/tasks', [
            'project_id' => $this->project->id,
            'title' => 'Test Task',
            'description' => 'Task description',
            'priority' => 'high',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        // dd($response->json());

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Test Task')
            ->assertJsonPath('data.project_id', $this->project->id);

        $this->assertDatabaseHas('tasks', ['title' => 'Test Task']);
    }

    /** @test */
    public function test_user_can_view_tasks_list()
    {
        Task::factory()->count(3)->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function test_user_can_filter_tasks_by_status()
    {
        Task::factory()->create(['project_id' => $this->project->id, 'status' => 'new']);
        Task::factory()->create(['project_id' => $this->project->id, 'status' => 'done']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/tasks?status=new');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'new');
    }

    /** @test */
    public function test_user_can_filter_tasks_by_priority()
    {
        Task::factory()->create(['project_id' => $this->project->id, 'priority' => 'high']);
        Task::factory()->create(['project_id' => $this->project->id, 'priority' => 'low']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/tasks?priority=high');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.priority', 'high');
    }

    /** @test */
    public function test_user_can_search_tasks_by_title()
    {
        Task::factory()->create(['project_id' => $this->project->id, 'title' => 'Important Task']);
        Task::factory()->create(['project_id' => $this->project->id, 'title' => 'Regular Task']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/tasks?search=Important');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Important Task');
    }

    /** @test */
    public function test_user_can_update_task()
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Updated Task Title',
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Task Title')
            ->assertJsonPath('data.status', 'in_progress');
    }

    /** @test */
    public function test_user_can_delete_task()
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Task deleted successfully']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}