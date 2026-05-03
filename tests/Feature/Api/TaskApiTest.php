<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_create_task(): void
    {
        [$member, $project] = $this->memberAndProject();

        $response = $this->actingAs($member)->postJson('/api/v1/tasks', [
            'project_id' => $project->id,
            'title' => 'Implement API',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('tasks', 1);
    }

    public function test_viewer_cannot_create_task(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->members()->attach($viewer->id, ['role' => 'viewer']);

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($viewer)->postJson('/api/v1/tasks', [
            'project_id' => $project->id,
            'title' => 'Forbidden create',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $response->assertForbidden();
    }

    public function test_member_can_update_task_and_delete_forbidden(): void
    {
        [$member, $project] = $this->memberAndProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'creator_id' => $member->id,
        ]);

        $updateResponse = $this->actingAs($member)->patchJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Updated title',
            'status' => 'in_progress',
        ]);

        $updateResponse->assertOk()->assertJsonPath('data.title', 'Updated title');

        $deleteResponse = $this->actingAs($member)->deleteJson("/api/v1/tasks/{$task->id}");
        $deleteResponse->assertForbidden();
    }

    public function test_validation_error_returns_422(): void
    {
        [$member, $project] = $this->memberAndProject();

        $response = $this->actingAs($member)->postJson('/api/v1/tasks', [
            'project_id' => $project->id,
            'title' => 'x',
            'status' => 'unknown',
            'priority' => 'high',
        ]);

        $response->assertUnprocessable();
    }

    private function memberAndProject(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->members()->attach($member->id, ['role' => 'member']);

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);

        return [$member, $project];
    }
}
