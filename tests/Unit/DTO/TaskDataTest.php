<?php

namespace Tests\Unit\DTO;

use App\DTOs\TaskData;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Tests\TestCase;

class TaskDataTest extends TestCase
{
    public function test_rules_accept_valid_payload(): void
    {
        $validator = Validator::make([
            'project_id' => 1,
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
