<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * @return CursorPaginator<int, Task>
     */
    public function paginateTasks(?string $status = null, int $perPage = 10): CursorPaginator
    {
        $query = Task::query();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->latest()->cursorPaginate($perPage);
    }

    public function findById(int $id): ?Task
    {
        return Task::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Task
    {
        return Task::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}
