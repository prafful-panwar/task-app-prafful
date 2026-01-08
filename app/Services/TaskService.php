<?php

namespace App\Services;

use App\DTOs\Task\CreateTaskDTO;
use App\DTOs\Task\UpdateTaskDTO;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    /**
     * Get tasks with optional status filter.
     *
     * @return LengthAwarePaginator<Task>
     */
    public function getTasks(?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::query();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a single task by ID.
     */
    public function getTaskById(int $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * Create a new task.
     */
    public function createTask(CreateTaskDTO $dto): Task
    {
        return Task::create($dto->toArray());
    }

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, UpdateTaskDTO $dto): Task
    {
        $task->update($dto->toArray());

        return $task->fresh();
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }
}
