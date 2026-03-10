<?php

use App\Enums\TaskStatus;
use App\Models\Task;

test('can list all tasks', function (): void {

    Task::factory()->count(5)->create([]);

    $response = $this->getJson('/api/tasks');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'status_label',
                    'due_date',
                    'due_date_human',

                    'created_at',
                    'created_at_human',
                    'updated_at',
                ],
            ],
        ]);

    expect($response->json('data'))->toHaveCount(5);
});

test('can filter tasks by status', function (): void {

    Task::factory()->create(['status' => 'pending']);
    Task::factory()->create(['status' => 'in_progress']);
    Task::factory()->create(['status' => 'completed']);

    $response = $this->getJson('/api/tasks?status=pending');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.status'))->toBe('pending');
});

test('can paginate tasks', function (): void {

    for ($i = 0; $i < 25; $i++) {
        Task::factory()->create([
            'created_at' => now()->subMinutes($i),
        ]);
    }

    $response = $this->getJson('/api/tasks?per_page=10');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ],
        ]);

    expect($response->json('data'))->toHaveCount(10);
    expect($response->json('meta.per_page'))->toBe(10);
    expect($response->json('meta.next_cursor'))->not->toBeNull();
});

test('can navigate paginated tasks', function (): void {

    for ($i = 0; $i < 25; $i++) {
        Task::factory()->create([
            'created_at' => now()->subMinutes($i),
        ]);
    }

    $response1 = $this->getJson('/api/tasks?per_page=10');
    $nextCursor = $response1->json('meta.next_cursor');

    $response2 = $this->getJson("/api/tasks?per_page=10&cursor={$nextCursor}");

    $response2->assertSuccessful();
    expect($response2->json('data'))->toHaveCount(10);
});

test('can create a new task', function (): void {

    $taskData = [
        'title' => 'Test Task',
        'description' => 'This is a test task',
        'status' => TaskStatus::Pending->value,
        'due_date' => now()->addMonth()->format('Y-m-d'),
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'due_date',
                'created_at',
                'updated_at',
            ],
        ]);

    expect($response->json('data.title'))->toBe('Test Task');
    expect($response->json('data.status'))->toBe('pending');

    $this->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
        'status' => TaskStatus::Pending->value,
    ]);
});

test('can create a task with minimal required fields', function (): void {

    $taskData = [
        'title' => 'Minimal Task',
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertCreated();
    expect($response->json('data.title'))->toBe('Minimal Task');
    expect($response->json('data.status'))->toBe('pending');
});

test('cannot create a task without title', function (): void {

    $taskData = [
        'description' => 'Task without title',
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('cannot create a task with invalid status', function (): void {

    $taskData = [
        'title' => 'Test Task',
        'status' => 'invalid_status',
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('cannot create a task with invalid date format', function (): void {

    $taskData = [
        'title' => 'Test Task',
        'due_date' => 'invalid-date',
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['due_date']);
});

test('can show a single task', function (): void {

    $task = Task::factory()->create([]);

    $response = $this->getJson("/api/tasks/{$task->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'due_date',
                'created_at',
                'updated_at',
            ],
        ]);

    expect($response->json('data.id'))->toBe($task->id);
    expect($response->json('data.title'))->toBe($task->title);
});

test('returns 404 when task not found', function (): void {

    $response = $this->getJson('/api/tasks/99999');

    $response->assertNotFound()
        ->assertJson([
            'message' => 'Task not found.',
        ]);
});

test('can update a task', function (): void {

    $task = Task::factory()->create([
        'title' => 'Original Title',
        'status' => TaskStatus::Pending->value,

    ]);

    $updateData = [
        'title' => 'Updated Title',
        'status' => TaskStatus::InProgress->value,
    ];

    $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

    $response->assertSuccessful();
    expect($response->json('data.title'))->toBe('Updated Title');
    expect($response->json('data.status'))->toBe('in_progress');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Title',
        'status' => TaskStatus::InProgress->value,
    ]);
});

test('can partially update a task', function (): void {

    $task = Task::factory()->create([
        'title' => 'Original Title',
        'status' => TaskStatus::Pending->value,

    ]);

    $updateData = [
        'status' => TaskStatus::Completed->value,
    ];

    $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

    $response->assertSuccessful();
    expect($response->json('data.status'))->toBe('completed');
    expect($response->json('data.title'))->toBe('Original Title');
});

test('returns 404 when updating non-existent task', function (): void {

    $updateData = [
        'title' => 'Updated Title',
    ];

    $response = $this->putJson('/api/tasks/99999', $updateData);

    $response->assertNotFound()
        ->assertJson([
            'message' => 'Task not found.',
        ]);
});

test('cannot update task with invalid status', function (): void {

    $task = Task::factory()->create([]);

    $updateData = [
        'status' => 'invalid_status',
    ];

    $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('can delete a task', function (): void {

    $task = Task::factory()->create([]);

    $response = $this->deleteJson("/api/tasks/{$task->id}");

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Task deleted successfully.',
        ]);

    $this->assertSoftDeleted('tasks', [
        'id' => $task->id,
    ]);
});

test('cannot create a task with past due date', function (): void {
    $taskData = [
        'title' => 'Test Task',
        'due_date' => now()->subDay()->format('Y-m-d'),
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['due_date']);
});
