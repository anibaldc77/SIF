<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Lock;

use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationLockException;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;

final readonly class PdoMigrationLockSql
{
    public function __construct(private PdoMigrationPlatform $platform)
    {
    }

    public function acquire(): string
    {
        return match ($this->platform->value()) {
            'postgresql' => 'SELECT pg_try_advisory_lock(:key_one, :key_two)',
            'mysql' => 'SELECT GET_LOCK(:resource, :timeout_seconds)',
            'sqlserver' => "DECLARE @result int; EXEC @result = sp_getapplock @Resource = :resource, @LockMode = 'Exclusive', @LockOwner = 'Session', @LockTimeout = :timeout_milliseconds; SELECT @result",
            default => throw new PdoMigrationLockException('Unsupported PDO migration lock platform.'),
        };
    }

    public function release(): string
    {
        return match ($this->platform->value()) {
            'postgresql' => 'SELECT pg_advisory_unlock(:key_one, :key_two)',
            'mysql' => 'SELECT RELEASE_LOCK(:resource)',
            'sqlserver' => "DECLARE @result int; EXEC @result = sp_releaseapplock @Resource = :resource, @LockOwner = 'Session'; SELECT @result",
            default => throw new PdoMigrationLockException('Unsupported PDO migration lock platform.'),
        };
    }
}
