<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_project_creation()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'Test Project',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['name' => 'Test Project']);
    }

    /** @test */
    public function test_projects_list()
    {
        $user = User::factory()->create();
        Project::factory()->count(3)->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}