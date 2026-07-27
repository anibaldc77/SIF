<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

final readonly class ChangeSet
{
    /**
     * @param list<object> $new
     * @param list<object> $dirty
     * @param list<object> $removed
     */
    public function __construct(
        private array $new = [],
        private array $dirty = [],
        private array $removed = [],
    ) {
    }

    /**
     * @return list<object>
     */
    public function newObjects(): array
    {
        return $this->new;
    }

    /**
     * @return list<object>
     */
    public function dirtyObjects(): array
    {
        return $this->dirty;
    }

    /**
     * @return list<object>
     */
    public function removedObjects(): array
    {
        return $this->removed;
    }

    public function isEmpty(): bool
    {
        return $this->new === []
            && $this->dirty === []
            && $this->removed === [];
    }

    public function count(): int
    {
        return count($this->new)
            + count($this->dirty)
            + count($this->removed);
    }
}
