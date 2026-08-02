<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Template;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;

final readonly class ModelTemplateOptions
{
    /** @var list<string> */
    private array $identityColumns;

    /** @param iterable<string> $identityColumns */
    public function __construct(
        iterable $identityColumns = ['id'],
        private bool $timestamps = true,
        private bool $softDeletes = false,
    ) {
        $normalized = [];
        foreach ($identityColumns as $column) {
            if (preg_match('/^[a-z][a-z0-9_]*$/', $column) !== 1) {
                throw new InvalidSkeletonValueException(sprintf('Invalid model identity column "%s".', $column));
            }
            $normalized[$column] = true;
        }

        if ($normalized === []) {
            throw new InvalidSkeletonValueException('At least one model identity column is required.');
        }

        $this->identityColumns = array_keys($normalized);
    }

    /** @return list<string> */
    public function identityColumns(): array
    {
        return $this->identityColumns;
    }

    public function timestamps(): bool
    {
        return $this->timestamps;
    }

    public function softDeletes(): bool
    {
        return $this->softDeletes;
    }
}
