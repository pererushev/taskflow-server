<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\TaskData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    public function index(Request $request, Project $project)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::query()
            ->where('project_id', $project->id)
            ->with(['creator', 'assignee'])
            ->orderBy('status')
            ->orderBy('order')
            ->paginate((int) $request->integer('per_page', 20));

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $project = Project::query()->findOrFail((int) $request->integer('project_id'));
        $this->authorize('create', Task::class);

        $role = $request->user()->teams()
            ->where('teams.id', $project->team_id)
            ->value('team_user.role');

        abort_if(! in_array($role, ['owner', 'admin', 'member'], true), 403);

        $data = TaskData::fromArray($request->validated(), (int) $request->user()->id);
        $task = $this->taskService->create($data);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['creator', 'assignee', 'project']));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $updated = $this->taskService->update($task, $request->validated());

        return new TaskResource($updated);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task);

        return response()->json([], 204);
    }
}
