<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Planning;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationPlanException;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;

final readonly class MigrationPlan
{
    /** @var list<MigrationDescriptor> */
    private array $migrations;
    private string $fingerprint;

    /** @param iterable<MigrationDescriptor> $migrations */
    public function __construct(private MigrationDirection $direction, iterable $migrations)
    {
        $normalized = [];
        $seen = [];
        foreach ($migrations as $migration) {
            if (!$migration instanceof MigrationDescriptor) {
                throw new InvalidMigrationPlanException('Migration plan members must be MigrationDescriptor values.');
            }
            $id = $migration->id()->value();
            if (isset($seen[$id])) {
                throw new InvalidMigrationPlanException(sprintf('Migration plan contains duplicate migration "%s".', $id));
            }
            $seen[$id] = true;
            $normalized[] = $migration;
        }
        $this->migrations = $normalized;
        $payload = [
            'direction' => $direction->value(),
            'migrations' => array_map(
                static fn (MigrationDescriptor $descriptor): array => $descriptor->summary(),
                $normalized,
            ),
        ];
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $this->fingerprint = hash('sha256', $json);
    }

    public function direction(): MigrationDirection
    {
        return $this->direction;
    }

    /** @return list<MigrationDescriptor> */
    public function migrations(): array
    {
        return $this->migrations;
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return array_map(static fn (MigrationDescriptor $migration): string => $migration->id()->value(), $this->migrations);
    }

    public function count(): int
    {
        return count($this->migrations);
    }

    public function fingerprint(): string
    {
        return $this->fingerprint;
    }
}
