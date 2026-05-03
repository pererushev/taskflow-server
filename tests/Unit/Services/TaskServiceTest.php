<?php

namespace Tests\Unit\Services;

use App\DTOs\TaskData;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_assigns_incremental_order_within_column(): void
    {
        $creator = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $creator->id]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $creator->id,
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'creator_id' => $creator->id,
            'status' => 'todo',
            'order' => 1000,
        ]);

        $service = app(TaskService::class);
        $created = $service->create(new TaskData(
            projectId: $project->id,
            creatorId: $creator->id,
            assigneeId: null,
            title: 'New task',
            description: null,
            status: 'todo',
            priority: 'medium',
            dueDate: null,
        ));

        $this->assertSame('2000.0000', (string) $created->order);
    }
}
