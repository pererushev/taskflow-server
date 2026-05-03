<?php

namespace Tests\Unit\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_view_but_cannot_update_or_delete(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->members()->attach($viewer->id, ['role' => 'viewer']);

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'creator_id' => $owner->id,
        ]);

        $policy = new TaskPolicy();

        $this->assertTrue($policy->view($viewer, $task));
        $this->assertFalse($policy->update($viewer, $task));
        $this->assertFalse($policy->delete($viewer, $task));
    }
}
