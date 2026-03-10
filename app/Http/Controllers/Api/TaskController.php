<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Task\CreateTaskDTO;
use App\DTOs\Task\UpdateTaskDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TaskService $taskService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskService->getTasks(
            $request->query('status'),
            (int) $request->query('per_page', 10)
        );

        return TaskResource::collection($tasks)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $dto = CreateTaskDTO::fromArray($request->validated());
        $task = $this->taskService->createTask($dto);

        return response()->json(['data' => new TaskResource($task)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $task = $this->taskService->getTaskByIdOrFail((int) $id);

        $this->authorize('view', $task);

        return response()->json(['data' => new TaskResource($task)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, string $id): JsonResponse
    {
        $task = $this->taskService->getTaskByIdOrFail((int) $id);

        $this->authorize('update', $task);

        $dto = UpdateTaskDTO::fromArray($request->validated());
        $task = $this->taskService->updateTask($task, $dto);

        return response()->json(['data' => new TaskResource($task)]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $task = $this->taskService->getTaskByIdOrFail((int) $id);

        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return response()->json(['message' => 'Task deleted successfully.'], 200);
    }
}
