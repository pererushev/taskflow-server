<?php

namespace App\DTOs;

use Illuminate\Validation\Rule;
use InvalidArgumentException;

class TaskData
{
    public function __construct(
        public readonly int $projectId,
        public readonly int $creatorId,
        public readonly ?int $assigneeId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $status,
        public readonly string $priority,
        public readonly ?string $dueDate,
    ) {
    }

    public static function rules(bool $isUpdate = false): array
    {
        $requiredOrSometimes = $isUpdate ? 'sometimes' : 'required';

        return [
            'project_id' => [$requiredOrSometimes, 'integer', 'exists:projects,id'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => [$requiredOrSometimes, 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => [$requiredOrSometimes, Rule::in(['todo', 'in_progress', 'review', 'done'])],
            'priority' => [$requiredOrSometimes, Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public static function fromArray(array $payload, int $creatorId): self
    {
        if (! isset($payload['project_id'])) {
            throw new InvalidArgumentException('project_id is required for task data creation.');
        }

        return new self(
            projectId: (int) $payload['project_id'],
            creatorId: $creatorId,
            assigneeId: isset($payload['assignee_id']) ? (int) $payload['assignee_id'] : null,
            title: (string) ($payload['title'] ?? ''),
            description: $payload['description'] ?? null,
            status: (string) ($payload['status'] ?? 'todo'),
            priority: (string) ($payload['priority'] ?? 'medium'),
            dueDate: $payload['due_date'] ?? null,
        );
    }
}
