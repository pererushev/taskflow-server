<?php

namespace Tests\Unit\DTO;

use App\DTOs\TaskData;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Tests\TestCase;

class TaskDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_rules_accept_valid_payload(): void
    {
        $creator = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $creator->id]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $creator->id,
        ]);

        $validator = Validator::make([
            'project_id' => $project->id,
            'title' => 'Implement API endpoint',
            'status' => 'todo',
            'priority' => 'medium',
        ], TaskData::rules());

        $this->assertFalse($validator->fails());
    }

    public function test_from_array_throws_when_project_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TaskData::fromArray([
            'title' => 'Task without project',
            'status' => 'todo',
            'priority' => 'low',
        ], creatorId: 10);
    }
}
