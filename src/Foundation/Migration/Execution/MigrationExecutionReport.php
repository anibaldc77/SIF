<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Execution;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationExecutionReportException;
use Sif\Foundation\Migration\MigrationDirection;

final readonly class MigrationExecutionReport
{
    /** @var list<MigrationExecutionEntry> */
    private array $entries;

    /**
     * @param iterable<MigrationExecutionEntry> $entries
     */
    public function __construct(
        private string $planFingerprint,
        private MigrationDirection $direction,
        iterable $entries,
    ) {
        if (preg_match('/^[a-f0-9]{64}$/D', $planFingerprint) !== 1) {
            throw new InvalidMigrationExecutionReportException('Execution report fingerprint must be SHA-256.');
        }

        $normalized = [];
        foreach ($entries as $entry) {
            if (!$entry instanceof MigrationExecutionEntry) {
                throw new InvalidMigrationExecutionReportException(
                    'Execution report entries must contain only MigrationExecutionEntry values.',
                );
            }
            if ($entry->sequence() !== count($normalized) + 1) {
                throw new InvalidMigrationExecutionReportException('Execution report sequence must be contiguous.');
            }
            $normalized[] = $entry;
        }
        $this->entries = $normalized;
    }

    public function planFingerprint(): string
    {
        return $this->planFingerprint;
    }

    public function direction(): MigrationDirection
    {
        return $this->direction;
    }

    /** @return list<MigrationExecutionEntry> */
    public function entries(): array
    {
        return $this->entries;
    }

    public function successful(): bool
    {
        foreach ($this->entries as $entry) {
            if (!$entry->successful()) {
                return false;
            }
        }
        return true;
    }

    public function completedCount(): int
    {
        $count = 0;
        foreach ($this->entries as $entry) {
            if ($entry->successful()) {
                ++$count;
            }
        }
        return $count;
    }

    /** @return array{fingerprint: string, direction: string, successful: bool, completed: int, entries: list<array{sequence: int, id: string, status: string, code: string|null}>} */
    public function summary(): array
    {
        return [
            'fingerprint' => $this->planFingerprint,
            'direction' => $this->direction->value(),
            'successful' => $this->successful(),
            'completed' => $this->completedCount(),
            'entries' => array_map(
                static fn (MigrationExecutionEntry $entry): array => $entry->summary(),
                $this->entries,
            ),
        ];
    }
}
