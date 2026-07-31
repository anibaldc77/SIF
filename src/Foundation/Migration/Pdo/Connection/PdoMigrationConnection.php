<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Connection;

use PDO;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationConnectionException;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationCapabilities;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;

final readonly class PdoMigrationConnection
{
    public function __construct(
        private PDO $pdo,
        private PdoMigrationConnectionName $name,
        private PdoMigrationPlatform $platform,
        private PdoMigrationConnectionOwnership $ownership,
        private PdoMigrationCapabilities $capabilities,
    ) {
        if (!$this->platform->equals($this->capabilities->platform())) {
            throw new InvalidPdoMigrationConnectionException(
                'PDO migration connection platform must match the capability profile platform.',
            );
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function name(): PdoMigrationConnectionName
    {
        return $this->name;
    }

    public function platform(): PdoMigrationPlatform
    {
        return $this->platform;
    }

    public function ownership(): PdoMigrationConnectionOwnership
    {
        return $this->ownership;
    }

    public function capabilities(): PdoMigrationCapabilities
    {
        return $this->capabilities;
    }

    /** @return array<string, bool|string|array<string, bool|string|list<string>>> */
    public function summary(): array
    {
        return [
            'name' => $this->name->value(),
            'platform' => $this->platform->value(),
            'driver' => $this->platform->driver(),
            'ownership' => $this->ownership->value(),
            'externally_owned' => $this->ownership->externallyOwned(),
            'capabilities' => $this->capabilities->summary(),
        ];
    }
}
