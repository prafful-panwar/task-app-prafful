<?php

namespace App\DTOs\Task;

use App\Enums\TaskStatus;

class CreateTaskDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly TaskStatus $status,
        public readonly ?string $dueDate,
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            status: isset($data['status']) ? TaskStatus::from($data['status']) : TaskStatus::Pending,
            dueDate: $data['due_date'] ?? null,
        );
    }

    /**
     * Convert DTO to array for model creation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'due_date' => $this->dueDate,
        ];
    }
}
