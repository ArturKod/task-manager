<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_changing_task_status_creates_audit_log()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'new',
        ]);

        $this->actingAs($user)
            ->putJson("/api/v1/tasks/{$task->id}", ['status' => 'in_progress']);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => Task::class,
            'entity_id' => $task->id,
            'action' => 'status_changed',
        ]);

        $log = \App\Models\AuditLog::where('entity_id', $task->id)->first();
        $this->assertEquals(['old_status' => 'new', 'new_status' => 'in_progress'], $log->meta);
    }

    /** @test */
    public function test_completing_task_creates_audit_log()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->putJson("/api/v1/tasks/{$task->id}", ['status' => 'done']);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => Task::class,
            'entity_id' => $task->id,
            'action' => 'completed',
        ]);
    }
}