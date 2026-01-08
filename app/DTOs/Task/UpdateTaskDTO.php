<?php

namespace App\DTOs\Task;

class UpdateTaskDTO
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?\App\Enums\TaskStatus $status,
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
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            status: isset($data['status']) ? \App\Enums\TaskStatus::from($data['status']) : null,
            dueDate: $data['due_date'] ?? null,
        );
    }

    /**
     * Convert DTO to array for model update.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->title !== null) {
            $data['title'] = $this->title;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status->value;
        }

        if ($this->dueDate !== null) {
            $data['due_date'] = $this->dueDate;
        }

        return $data;
    }
}
