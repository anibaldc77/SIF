<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Exceptions\InvalidServiceTagException;

final readonly class ServiceTag
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function __construct(
        private string $name,
        private int $priority = 0,
        private array $metadata = [],
    ) {
        if (trim($this->name) === '') {
            throw new InvalidServiceTagException(
                'Service tag name cannot be empty.',
            );
        }

        foreach (array_keys($this->metadata) as $key) {
            if (trim($key) === '') {
                throw new InvalidServiceTagException(
                    'Service tag metadata key cannot be empty.',
                );
            }
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    /**
     * @return array<string, scalar|null>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
