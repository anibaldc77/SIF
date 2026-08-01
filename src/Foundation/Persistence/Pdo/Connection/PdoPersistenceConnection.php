<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Connection;

use PDO;
use Sif\Foundation\Contracts\ConnectionInterface;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoPersistenceConnectionException;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistenceCapabilities;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;

final class PdoPersistenceConnection implements ConnectionInterface
{
    private bool $open = true;

    public function __construct(
        private PDO $pdo,
        private readonly ConnectionName $name,
        private readonly PdoPersistencePlatform $platform,
        private readonly PdoPersistenceConnectionOwnership $ownership,
        private readonly PdoPersistenceCapabilities $capabilities,
    ) {
        if (!$this->platform->equals($this->capabilities->platform())) {
            throw new InvalidPdoPersistenceConnectionException(
                'PDO persistence connection platform must match the capability profile platform.',
            );
        }
    }

    public function pdo(): PDO
    {
        if (!$this->open) {
            throw new InvalidPdoPersistenceConnectionException('PDO persistence connection is closed.');
        }
        return $this->pdo;
    }

    public function name(): ConnectionName { return $this->name; }
    public function platform(): PdoPersistencePlatform { return $this->platform; }
    public function ownership(): PdoPersistenceConnectionOwnership { return $this->ownership; }
    public function capabilities(): PdoPersistenceCapabilities { return $this->capabilities; }
    public function isOpen(): bool { return $this->open; }

    public function close(): void
    {
        $this->open = false;
        if (!$this->ownership->externallyOwned()) {
            unset($this->pdo);
        }
    }

    /** @return array<string, bool|string|array<string, bool|int|string|list<string>>> */
    public function summary(): array
    {
        return [
            'name' => $this->name->value(),
            'platform' => $this->platform->value(),
            'driver' => $this->platform->driver(),
            'ownership' => $this->ownership->value(),
            'externally_owned' => $this->ownership->externallyOwned(),
            'open' => $this->open,
            'capabilities' => $this->capabilities->summary(),
        ];
    }
}
