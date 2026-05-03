<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Пользователи
        $owner = User::query()->firstOrCreate(
            ['email' => 'owner@taskflow.test'],
            ['name' => 'Алексей', 'password' => Hash::make('password')]
        );
        $member = User::query()->firstOrCreate(
            ['email' => 'member@taskflow.test'],
            ['name' => 'Мария', 'password' => Hash::make('password')]
        );
        $viewer = User::query()->firstOrCreate(
            ['email' => 'viewer@taskflow.test'],
            ['name' => 'Иван', 'password' => Hash::make('password')]
        );

        // Команда с ролями
        $team = Team::query()->firstOrCreate(
            ['slug' => 'taskflow-team'],
            ['name' => 'TaskFlow Team', 'owner_id' => $owner->id]
        );
        $team->members()->syncWithoutDetaching([
            $owner->id  => ['role' => 'owner'],
            $member->id => ['role' => 'member'],
            $viewer->id => ['role' => 'viewer'],
        ]);

        // Проект
        $project = Project::query()->firstOrCreate(
            ['team_id' => $team->id, 'name' => 'MVP Launch'],
            ['created_by' => $owner->id, 'description' => 'Подготовка к релизу первой версии.']
        );

        // Задачи (используем состояния фабрик)
        $tasks = [
            ['title' => 'Настроить CI/CD', 'status' => 'done', 'priority' => 'high', 'assignee' => $owner],
            ['title' => 'Реализовать Kanban-доску', 'status' => 'in_progress', 'priority' => 'urgent', 'assignee' => $member],
            ['title' => 'Добавить экспорт в CSV', 'status' => 'todo', 'priority' => 'medium', 'assignee' => null],
            ['title' => 'Написать тесты для API', 'status' => 'review', 'priority' => 'high', 'assignee' => $member],
            ['title' => 'Оптимизировать запросы', 'status' => 'todo', 'priority' => 'low', 'assignee' => $owner],
        ];

        foreach ($tasks as $index => $taskData) {
            Task::query()->updateOrCreate(
                ['project_id' => $project->id, 'title' => $taskData['title']],
                [
                'creator_id' => $owner->id,
                'order' => ($index + 1) * 1000, // 1000, 2000, 3000...
                'status' => $taskData['status'],
                'priority' => $taskData['priority'],
                'assignee_id' => $taskData['assignee']?->id,
                'due_date' => now()->addDays(rand(1, 14)),
                ]
            );
        }
    }
}
