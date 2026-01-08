<?php

use App\Models\Task;



test('can list all tasks', function () {

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

test('can filter tasks by status', function () {

    Task::factory()->create(['status' => 'pending']);
    Task::factory()->create(['status' => 'in_progress']);
    Task::factory()->create(['status' => 'completed']);

    $response = $this->getJson('/api/tasks?status=pending');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.status'))->toBe('pending');
});

test('can paginate tasks', function () {

    Task::factory()->count(25)->create([]);

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
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);

    expect($response->json('data'))->toHaveCount(10);
    expect($response->json('meta.per_page'))->toBe(10);
    expect($response->json('meta.total'))->toBe(25);
    expect($response->json('meta.last_page'))->toBe(3);
});

test('can navigate paginated tasks', function () {

    Task::factory()->count(25)->create([]);

    $response = $this->getJson('/api/tasks?per_page=10&page=2');

    $response->assertSuccessful();
    expect($response->json('meta.current_page'))->toBe(2);
    expect($response->json('data'))->toHaveCount(10);
});

test('can create a new task', function () {

    $taskData = [
        'title' => 'Test Task',
        'description' => 'This is a test task',
        'status' => \App\Enums\TaskStatus::Pending->value,
        'due_date' => '2024-12-31',
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
        'status' => \App\Enums\TaskStatus::Pending->value,
    ]);
});

test('can create a task with minimal required fields', function () {

    $taskData = [
        'title' => 'Minimal Task',
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertCreated();
    expect($response->json('data.title'))->toBe('Minimal Task');
    expect($response->json('data.status'))->toBe('pending');
});

test('cannot create a task without title', function () {

    $taskData = [
        'description' => 'Task without title',
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('cannot create a task with invalid status', function () {

    $taskData = [
        'title' => 'Test Task',
        'status' => 'invalid_status',
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('cannot create a task with invalid date format', function () {

    $taskData = [
        'title' => 'Test Task',
        'due_date' => 'invalid-date',
    ];

    $response = $this->postJson('/api/tasks', $taskData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['due_date']);
});

test('can show a single task', function () {

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

test('returns 404 when task not found', function () {

    $response = $this->getJson('/api/tasks/99999');

    $response->assertNotFound()
        ->assertJson([
            'message' => 'Task not found.',
        ]);
});

test('can update a task', function () {

    $task = Task::factory()->create([
        'title' => 'Original Title',
        'status' => \App\Enums\TaskStatus::Pending->value,

    ]);

    $updateData = [
        'title' => 'Updated Title',
        'status' => \App\Enums\TaskStatus::InProgress->value,
    ];

    $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

    $response->assertSuccessful();
    expect($response->json('data.title'))->toBe('Updated Title');
    expect($response->json('data.status'))->toBe('in_progress');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Title',
        'status' => \App\Enums\TaskStatus::InProgress->value,
    ]);
});

test('can partially update a task', function () {

    $task = Task::factory()->create([
        'title' => 'Original Title',
        'status' => \App\Enums\TaskStatus::Pending->value,

    ]);

    $updateData = [
        'status' => \App\Enums\TaskStatus::Completed->value,
    ];

    $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

    $response->assertSuccessful();
    expect($response->json('data.status'))->toBe('completed');
    expect($response->json('data.title'))->toBe('Original Title');
});

test('returns 404 when updating non-existent task', function () {

    $updateData = [
        'title' => 'Updated Title',
    ];

    $response = $this->putJson('/api/tasks/99999', $updateData);

    $response->assertNotFound()
        ->assertJson([
            'message' => 'Task not found.',
        ]);
});

test('cannot update task with invalid status', function () {

    $task = Task::factory()->create([]);

    $updateData = [
        'status' => 'invalid_status',
    ];

    $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('can delete a task', function () {

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

test('returns 404 when deleting non-existent task', function () {

    $response = $this->deleteJson('/api/tasks/99999');

    $response->assertNotFound()
        ->assertJson([
            'message' => 'Task not found.',
        ]);
});
