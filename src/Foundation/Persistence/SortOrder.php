<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

final readonly class SortOrder
{
    /**
     * @var list<SortField>
     */
    private array $fields;

    /**
     * @param list<SortField> $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = array_values($fields);
    }

    /**
     * @return list<SortField>
     */
    public function all(): array
    {
        return $this->fields;
    }

    public function isEmpty(): bool
    {
        return $this->fields === [];
    }

    public function with(SortField $field): self
    {
        return new self([...$this->fields, $field]);
    }
}
