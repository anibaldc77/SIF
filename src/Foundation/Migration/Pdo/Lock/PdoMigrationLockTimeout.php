<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Lock;

use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationLockTimeoutException;

final readonly class PdoMigrationLockTimeout
{
    public function __construct(private int $milliseconds = 0)
    {
        if ($this->milliseconds < 0 || $this->milliseconds > 2_147_483_647) {
            throw new InvalidPdoMigrationLockTimeoutException(
                'PDO migration lock timeout must be between 0 and 2147483647 milliseconds.',
            );
        }
    }

    public static function noWait(): self
    {
        return new self(0);
    }

    public function milliseconds(): int
    {
        return $this->milliseconds;
    }

    public function mysqlSeconds(): int
    {
        return (int) ceil($this->milliseconds / 1000);
    }
}
