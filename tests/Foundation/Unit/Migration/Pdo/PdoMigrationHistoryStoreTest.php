<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration\Pdo;

use DateTimeImmutable;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\History\MigrationHistoryStatus;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationVersion;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionName;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionOwnership;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationHistoryTableException;
use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationHistoryStorageException;
use Sif\Foundation\Migration\Pdo\History\PdoMigrationHistorySql;
use Sif\Foundation\Migration\Pdo\History\PdoMigrationHistoryStore;
use Sif\Foundation\Migration\Pdo\History\PdoMigrationHistoryTable;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationCapabilities;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;

final class PdoMigrationHistoryStoreTest extends TestCase
{
    public function testTableIdentityRejectsUnsafeIdentifiersAndQuotesPerPlatform(): void
    {
        $table = new PdoMigrationHistoryTable('migration_history', 'sif');

        self::assertSame('"sif"."migration_history"', $table->qualified(PdoMigrationPlatform::postgresql()));
        self::assertSame('`sif`.`migration_history`', $table->qualified(PdoMigrationPlatform::mysql()));
        self::assertSame('[sif].[migration_history]', $table->qualified(PdoMigrationPlatform::sqlserver()));

        $this->expectException(InvalidPdoMigrationHistoryTableException::class);
        new PdoMigrationHistoryTable('migration-history;drop');
    }

    public function testSqlCompilerPreservesPlatformSpecificCreationRules(): void
    {
        $table = new PdoMigrationHistoryTable();

        $postgresql = new PdoMigrationHistorySql(PdoMigrationPlatform::postgresql(), $table);
        $mysql = new PdoMigrationHistorySql(PdoMigrationPlatform::mysql(), $table);
        $sqlserver = new PdoMigrationHistorySql(PdoMigrationPlatform::sqlserver(), $table);

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $postgresql->createTable());
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $mysql->createTable());
        self::assertStringContainsString('OBJECT_ID', $sqlserver->createTable());
        self::assertStringContainsString(':migration_id', $postgresql->selectOne());
    }

    public function testStoreInitializesOnlyOnceAndHydratesHistory(): void
    {
        $pdo = new RecordingHistoryPdo();
        $pdo->queryRows = [[
            'migration_id' => 'users.create',
            'checksum' => 'sha256:' . str_repeat('a', 64),
            'status' => 'applied',
            'recorded_at' => '2026-07-30T12:00:00+00:00',
            'migration_version' => '1.0.0',
            'batch' => 'batch-1',
        ]];

        $store = new PdoMigrationHistoryStore($this->connection($pdo));
        $history = $store->history();
        $store->history();

        self::assertSame(1, $pdo->execCount);
        self::assertSame(['users.create'], $history->identifiers());
        self::assertSame('1.0.0', $history->records()[0]->version()?->value());
        self::assertSame('batch-1', $history->records()[0]->batch());
    }

    public function testFindAppendAndRemoveUseGovernedParameters(): void
    {
        $pdo = new RecordingHistoryPdo();
        $pdo->preparedRows = [[
            'migration_id' => 'audit.create',
            'checksum' => 'sha256:' . str_repeat('b', 64),
            'status' => 'applied',
            'recorded_at' => '2026-07-30T12:10:00+00:00',
            'migration_version' => null,
            'batch' => null,
        ]];

        $store = new PdoMigrationHistoryStore($this->connection($pdo), autoInitialize: false);
        $found = $store->find(new MigrationId('audit.create'));

        self::assertSame('audit.create', $found?->id()->value());
        self::assertSame(['migration_id' => 'audit.create'], $pdo->executions[0]);

        $record = new MigrationHistoryRecord(
            new MigrationId('users.create'),
            new MigrationChecksum('sha256', str_repeat('c', 64)),
            MigrationHistoryStatus::applied(),
            new DateTimeImmutable('2026-07-30T12:20:00+00:00'),
            new MigrationVersion('2.0.0'),
            'batch-2',
        );
        $store->append($record);
        $store->remove($record->id());

        self::assertSame('users.create', $pdo->executions[1]['migration_id']);
        self::assertSame('sha256:' . str_repeat('c', 64), $pdo->executions[1]['checksum']);
        self::assertSame(['migration_id' => 'users.create'], $pdo->executions[2]);
    }

    public function testInvalidPersistedRowFailsClosedWithStorageException(): void
    {
        $pdo = new RecordingHistoryPdo();
        $pdo->queryRows = [[
            'migration_id' => 'broken',
            'checksum' => 'not-a-checksum',
            'status' => 'applied',
            'recorded_at' => '2026-07-30T12:00:00+00:00',
            'migration_version' => null,
            'batch' => null,
        ]];

        $this->expectException(PdoMigrationHistoryStorageException::class);
        (new PdoMigrationHistoryStore($this->connection($pdo)))->history();
    }

    private function connection(PDO $pdo): PdoMigrationConnection
    {
        return new PdoMigrationConnection(
            $pdo,
            new PdoMigrationConnectionName('primary'),
            PdoMigrationPlatform::postgresql(),
            PdoMigrationConnectionOwnership::external(),
            PdoMigrationCapabilities::postgresql(),
        );
    }
}

final class RecordingHistoryPdo extends PDO
{
    public int $execCount = 0;

    /** @var list<array<string, mixed>> */
    public array $queryRows = [];

    /** @var list<array<string, mixed>> */
    public array $preparedRows = [];

    /** @var list<array<string, string|null>> */
    public array $executions = [];

    public function __construct()
    {
    }

    public function exec(string $statement): int|false
    {
        ++$this->execCount;

        return 0;
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        return new RecordingHistoryStatement($this, $this->queryRows);
    }

    /** @param array<mixed> $options */
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new RecordingHistoryStatement($this, $this->preparedRows);
    }
}

final class RecordingHistoryStatement extends PDOStatement
{
    /** @param list<array<string, mixed>> $rows */
    public function __construct(
        private readonly RecordingHistoryPdo $pdo,
        private readonly array $rows,
    ) {
    }

    /** @param array<string, string|null>|null $params */
    public function execute(?array $params = null): bool
    {
        $this->pdo->executions[] = $params ?? [];

        return true;
    }

    /** @return array<mixed> */
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->rows;
    }

    public function fetch(
        int $mode = PDO::FETCH_DEFAULT,
        int $cursorOrientation = PDO::FETCH_ORI_NEXT,
        int $cursorOffset = 0,
    ): mixed {
        return $this->rows[0] ?? false;
    }
}
