<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Execution;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationExecutionEntryException;
use Sif\Foundation\Migration\MigrationId;

final readonly class MigrationExecutionEntry
{
    public function __construct(
        private int $sequence,
        private MigrationId $id,
        private bool $successful,
        private ?string $code = null,
    ) {
        if ($sequence < 1) {
            throw new InvalidMigrationExecutionEntryException('Migration execution sequence must be positive.');
        }
        if ($successful && $code !== null) {
            throw new InvalidMigrationExecutionEntryException('Successful execution entry cannot contain a failure code.');
        }
        if (!$successful && ($code === null || preg_match('/^[A-Z][A-Z0-9_]{1,63}$/D', $code) !== 1)) {
            throw new InvalidMigrationExecutionEntryException('Failed execution entry requires a safe failure code.');
        }
    }

    public function sequence(): int
    {
        return $this->sequence;
    }

    public function id(): MigrationId
    {
        return $this->id;
    }

    public function successful(): bool
    {
        return $this->successful;
    }

    public function code(): ?string
    {
        return $this->code;
    }

    /** @return array{sequence: int, id: string, status: string, code: string|null} */
    public function summary(): array
    {
        return [
            'sequence' => $this->sequence,
            'id' => $this->id->value(),
            'status' => $this->successful ? 'succeeded' : 'failed',
            'code' => $this->code,
        ];
    }
}
