<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Registry;

use Sif\Foundation\Migration\Exceptions\DuplicateMigrationException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationRegistryException;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationId;

final readonly class MigrationRegistry
{
    /** @var array<string,MigrationDescriptor> */
    private array $descriptors;

    /** @param iterable<MigrationDescriptor> $descriptors */
    public function __construct(iterable $descriptors = [])
    {
        $registered = [];
        foreach ($descriptors as $descriptor) {
            if (!$descriptor instanceof MigrationDescriptor) {
                throw new InvalidMigrationRegistryException('Migration registry members must be MigrationDescriptor values.');
            }
            $id = $descriptor->id()->value();
            if (isset($registered[$id])) {
                throw new DuplicateMigrationException(sprintf('Migration "%s" is registered more than once.', $id));
            }
            $registered[$id] = $descriptor;
        }
        ksort($registered, SORT_STRING);
        $this->descriptors = $registered;
    }

    public function has(MigrationId $id): bool
    {
        return isset($this->descriptors[$id->value()]);
    }

    public function get(MigrationId $id): ?MigrationDescriptor
    {
        return $this->descriptors[$id->value()] ?? null;
    }

    /** @return list<MigrationDescriptor> */
    public function all(): array
    {
        return array_values($this->descriptors);
    }

    public function count(): int
    {
        return count($this->descriptors);
    }
}
