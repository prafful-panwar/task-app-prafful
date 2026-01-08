<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Task\CreateTaskDTO;
use App\DTOs\Task\UpdateTaskDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 10);

        $tasks = $this->taskService->getTasks($status, $perPage);

        return TaskResource::collection($tasks)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $dto = CreateTaskDTO::fromArray($request->validated());
        $task = $this->taskService->createTask($dto);

        return response()->json([
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $task = $this->taskService->getTaskById((int) $id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found.',
            ], 404);
        }

        return response()->json([
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, string $id): JsonResponse
    {
        $task = $this->taskService->getTaskById((int) $id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found.',
            ], 404);
        }

        $dto = UpdateTaskDTO::fromArray($request->validated());
        $task = $this->taskService->updateTask($task, $dto);

        return response()->json([
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $task = $this->taskService->getTaskById((int) $id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found.',
            ], 404);
        }

        $this->taskService->deleteTask($task);

        return response()->json([
            'message' => 'Task deleted successfully.',
        ], 200);
    }
}
