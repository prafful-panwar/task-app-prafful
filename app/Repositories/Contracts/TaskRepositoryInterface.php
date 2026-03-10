<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface TaskRepositoryInterface
{
    /**
     * @return CursorPaginator<int, Task>
     */
    public function paginateTasks(?string $status = null, int $perPage = 10): CursorPaginator;

    public function findById(int $id): ?Task;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Task;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task;

    public function delete(Task $task): bool;
}
