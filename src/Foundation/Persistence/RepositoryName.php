<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Exceptions\InvalidRepositoryNameException;

final readonly class RepositoryName
{
    public function __construct(
        private string $value,
    ) {
        if (trim($this->value) === '') {
            throw new InvalidRepositoryNameException(
                'Repository name cannot be empty.',
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
