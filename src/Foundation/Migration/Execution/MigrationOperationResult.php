<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Execution;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationOperationResultException;

final readonly class MigrationOperationResult
{
    private function __construct(
        private bool $successful,
        private ?string $code,
    ) {
        if ($code !== null && preg_match('/^[A-Z][A-Z0-9_]{1,63}$/D', $code) !== 1) {
            throw new InvalidMigrationOperationResultException(
                'Migration operation result code must use safe uppercase vocabulary.',
            );
        }
        if ($successful && $code !== null) {
            throw new InvalidMigrationOperationResultException(
                'Successful migration operation result cannot contain a failure code.',
            );
        }
        if (!$successful && $code === null) {
            throw new InvalidMigrationOperationResultException(
                'Failed migration operation result requires a safe failure code.',
            );
        }
    }

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(string $code): self
    {
        return new self(false, $code);
    }

    public function successful(): bool
    {
        return $this->successful;
    }

    public function code(): ?string
    {
        return $this->code;
    }
}
