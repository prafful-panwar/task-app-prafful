<?php

namespace App\Services;

use App\DTOs\Task\CreateTaskDTO;
use App\DTOs\Task\UpdateTaskDTO;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Get tasks with optional status filter.
     *
     * @return CursorPaginator<int, Task>
     */
    public function getTasks(?string $status = null, int $perPage = 10): CursorPaginator
    {
        return $this->taskRepository->paginateTasks($status, $perPage);
    }

    /**
     * Get a single task by ID or throw a 404 JSON response.
     */
    public function getTaskByIdOrFail(int $id): Task
    {
        $task = $this->taskRepository->findById($id);

        if (! $task instanceof Task) {
            throw new HttpResponseException(
                response()->json(['message' => 'Task not found.'], 404)
            );
        }

        return $task;
    }

    /**
     * Create a new task.
     */
    public function createTask(CreateTaskDTO $dto): Task
    {
        return $this->taskRepository->create($dto->toArray());
    }

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, UpdateTaskDTO $dto): Task
    {
        return $this->taskRepository->update($task, $dto->toArray());
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): bool
    {
        return $this->taskRepository->delete($task);
    }
}
