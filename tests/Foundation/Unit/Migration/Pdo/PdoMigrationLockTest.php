<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration\Pdo;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionName;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionOwnership;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationLockResourceException;
use Sif\Foundation\Migration\Pdo\Lock\PdoMigrationLock;
use Sif\Foundation\Migration\Pdo\Lock\PdoMigrationLockResource;
use Sif\Foundation\Migration\Pdo\Lock\PdoMigrationLockSql;
use Sif\Foundation\Migration\Pdo\Lock\PdoMigrationLockTimeout;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationCapabilities;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;

final class PdoMigrationLockTest extends TestCase
{
    public function testResourceValidationAndPostgresqlKeyDerivationAreDeterministic(): void
    {
        $resource = new PdoMigrationLockResource('sif:migrations:primary');

        self::assertSame($resource->postgresqlKeys(), $resource->postgresqlKeys());
        self::assertCount(2, $resource->postgresqlKeys());

        $this->expectException(InvalidPdoMigrationLockResourceException::class);
        new PdoMigrationLockResource('bad resource;drop');
    }

    public function testSqlCompilerUsesNativePlatformLockPrimitives(): void
    {
        self::assertStringContainsString(
            'pg_try_advisory_lock',
            (new PdoMigrationLockSql(PdoMigrationPlatform::postgresql()))->acquire(),
        );
        self::assertStringContainsString(
            'GET_LOCK',
            (new PdoMigrationLockSql(PdoMigrationPlatform::mysql()))->acquire(),
        );
        self::assertStringContainsString(
            'sp_getapplock',
            (new PdoMigrationLockSql(PdoMigrationPlatform::sqlserver()))->acquire(),
        );
    }

    public function testAcquireAndReleaseTrackOwnerOnlyAfterDatabaseConfirmation(): void
    {
        $pdo = new RecordingLockPdo([1, 1]);
        $lock = new PdoMigrationLock(
            $this->connection($pdo, PdoMigrationPlatform::mysql()),
            new PdoMigrationLockResource('sif:migrations:test'),
            new PdoMigrationLockTimeout(1500),
        );

        self::assertTrue($lock->acquire('runner-1'));
        self::assertTrue($lock->held());
        self::assertSame('runner-1', $lock->owner());
        self::assertFalse($lock->acquire('runner-2'));

        $lock->release('runner-2');
        self::assertTrue($lock->held());

        $lock->release('runner-1');
        self::assertFalse($lock->held());
        self::assertSame(2, count($pdo->executions));
        self::assertSame(2, $pdo->executions[0]['timeout_seconds']);
    }

    public function testFailedDatabaseAcquisitionLeavesLockUnowned(): void
    {
        $pdo = new RecordingLockPdo([-1]);
        $lock = new PdoMigrationLock($this->connection($pdo, PdoMigrationPlatform::sqlserver()));

        self::assertFalse($lock->acquire('runner-1'));
        self::assertNull($lock->owner());
    }

    public function testPostgresqlUsesTwoDerivedIntegerKeys(): void
    {
        $pdo = new RecordingLockPdo([true]);
        $lock = new PdoMigrationLock(
            $this->connection($pdo, PdoMigrationPlatform::postgresql()),
            new PdoMigrationLockResource('sif:migrations:primary'),
        );

        self::assertTrue($lock->acquire('runner'));
        self::assertIsInt($pdo->executions[0]['key_one']);
        self::assertIsInt($pdo->executions[0]['key_two']);
    }

    private function connection(PDO $pdo, PdoMigrationPlatform $platform): PdoMigrationConnection
    {
        $capabilities = match ($platform->value()) {
            'postgresql' => PdoMigrationCapabilities::postgresql(),
            'mysql' => PdoMigrationCapabilities::mysql(),
            'sqlserver' => PdoMigrationCapabilities::sqlserver(),
            default => throw new \LogicException('Unsupported test platform.'),
        };

        return new PdoMigrationConnection(
            $pdo,
            new PdoMigrationConnectionName('primary'),
            $platform,
            PdoMigrationConnectionOwnership::external(),
            $capabilities,
        );
    }
}

final class RecordingLockPdo extends PDO
{
    /** @var list<mixed> */
    private array $results;

    /** @var list<array<string, int|string>> */
    public array $executions = [];

    /** @param list<mixed> $results */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /** @param array<mixed> $options */
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $result = array_shift($this->results);

        return new RecordingLockStatement($this, $result);
    }
}

final class RecordingLockStatement extends PDOStatement
{
    public function __construct(
        private readonly RecordingLockPdo $pdo,
        private readonly mixed $result,
    ) {
    }

    /** @param array<string, int|string>|null $params */
    public function execute(?array $params = null): bool
    {
        $this->pdo->executions[] = $params ?? [];

        return true;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        return $this->result;
    }
}
