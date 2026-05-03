<?php

namespace App\Services;

use App\DTOs\TaskData;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function create(TaskData $data): Task
    {
        return DB::transaction(function () use ($data): Task {
            $maxOrder = Task::query()
                ->where('project_id', $data->projectId)
                ->where('status', $data->status)
                ->max('order');

            $nextOrder = $maxOrder !== null ? ((float) $maxOrder + 1000.0) : 1000.0;

            $task = Task::query()->create([
                'project_id' => $data->projectId,
                'creator_id' => $data->creatorId,
                'assignee_id' => $data->assigneeId,
                'title' => $data->title,
                'description' => $data->description,
                'status' => $data->status,
                'priority' => $data->priority,
                'order' => $nextOrder,
                'due_date' => $data->dueDate,
            ]);

            return $task->load(['creator', 'assignee', 'project']);
        });
    }

    public function update(Task $task, array $payload): Task
    {
        $originalStatus = $task->status;
        $nextStatus = $payload['status'] ?? $originalStatus;

        return DB::transaction(function () use ($task, $payload, $originalStatus, $nextStatus): Task {
            $task->fill($payload);

            // If task moves to another column, append it to the bottom.
            if ($nextStatus !== $originalStatus) {
                $maxOrder = Task::query()
                    ->where('project_id', $task->project_id)
                    ->where('status', $nextStatus)
                    ->whereKeyNot($task->id)
                    ->max('order');

                $task->order = $maxOrder !== null ? ((float) $maxOrder + 1000.0) : 1000.0;
            }

            $task->save();

            return $task->refresh()->load(['creator', 'assignee', 'project']);
        });
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
