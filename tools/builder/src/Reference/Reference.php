<?php

declare(strict_types=1);

namespace Sif\Builder\Reference;

use InvalidArgumentException;

final readonly class Reference
{
    public function __construct(
        public string $sourceIdentifier,
        public string $targetIdentifier,
        public ReferenceType $type = ReferenceType::REFERENCE,
        public ?int $line = null,
        public ?int $column = null,
        public ?string $context = null,
    ) {
        if (trim($this->sourceIdentifier) === '') {
            throw new InvalidArgumentException('Reference source identifier cannot be empty.');
        }

        if (trim($this->targetIdentifier) === '') {
            throw new InvalidArgumentException('Reference target identifier cannot be empty.');
        }

        if ($this->line !== null && $this->line < 1) {
            throw new InvalidArgumentException('Reference line must be greater than zero.');
        }

        if ($this->column !== null && $this->column < 1) {
            throw new InvalidArgumentException('Reference column must be greater than zero.');
        }
    }

    public function identity(): string
    {
        return implode('|', [
            $this->sourceIdentifier,
            $this->targetIdentifier,
            $this->type->value,
            (string) ($this->line ?? ''),
            (string) ($this->column ?? ''),
        ]);
    }

    public function equals(self $other): bool
    {
        return $this->identity() === $other->identity()
            && $this->context === $other->context;
    }
}
