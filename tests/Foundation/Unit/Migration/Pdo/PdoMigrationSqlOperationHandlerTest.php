<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration\Pdo;

use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionName;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionOwnership;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationSqlOperationCatalogException;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationSqlOperationException;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationSqlStatementException;
use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationSqlExecutionException;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationCapabilities;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperation;
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationCatalog;
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationHandler;
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlStatement;

final class PdoMigrationSqlOperationHandlerTest extends TestCase
{
    public function testStatementValidatesSqlAndNamedParameters(): void
    {
        $statement = new PdoMigrationSqlStatement(
            'INSERT INTO users (id, active) VALUES (:id, :active)',
            ['id' => 10, 'active' => true],
        );

        self::assertSame(['id' => 10, 'active' => true], $statement->parameters());
        self::assertSame(2, $statement->summary()['parameter_count']);

        try {
            new PdoMigrationSqlStatement('');
            self::fail('Empty SQL must be rejected.');
        } catch (InvalidPdoMigrationSqlStatementException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidPdoMigrationSqlStatementException::class);
        new PdoMigrationSqlStatement('SELECT :bad', ['bad-name' => 1]);
    }

    public function testOperationRequiresUpStatementsAndReportsReversibility(): void
    {
        $operation = new PdoMigrationSqlOperation(
            new MigrationId('users.create'),
            [new PdoMigrationSqlStatement('CREATE TABLE users (id INT)')],
            [new PdoMigrationSqlStatement('DROP TABLE users')],
        );

        self::assertTrue($operation->reversible());
        self::assertCount(1, $operation->statements(MigrationDirection::up()));
        self::assertCount(1, $operation->statements(MigrationDirection::down()));

        $this->expectException(InvalidPdoMigrationSqlOperationException::class);
        new PdoMigrationSqlOperation(new MigrationId('empty.operation'), []);
    }

    public function testCatalogRejectsDuplicatesAndReturnsStableIdentifiers(): void
    {
        $operation = $this->operation();
        $catalog = new PdoMigrationSqlOperationCatalog([$operation]);

        self::assertTrue($catalog->has($operation->id()));
        self::assertSame(['users.create'], $catalog->identifiers());

        $this->expectException(InvalidPdoMigrationSqlOperationCatalogException::class);
        $catalog->register($operation);
    }

    public function testHandlerExecutesStatementsInOrderWithParameters(): void
    {
        $pdo = new RecordingSqlOperationPdo();
        $operation = new PdoMigrationSqlOperation(
            new MigrationId('users.create'),
            [
                new PdoMigrationSqlStatement('CREATE TABLE users (id INT)'),
                new PdoMigrationSqlStatement('INSERT INTO users (id) VALUES (:id)', ['id' => 7]),
            ],
            [new PdoMigrationSqlStatement('DROP TABLE users')],
        );
        $handler = $this->handler($pdo, $operation);
        $descriptor = $this->descriptor(true);

        self::assertTrue($handler->supports($descriptor));
        self::assertTrue($handler->execute($descriptor, MigrationDirection::up())->successful());
        self::assertSame(
            ['CREATE TABLE users (id INT)', 'INSERT INTO users (id) VALUES (:id)'],
            $pdo->preparedSql,
        );
        self::assertSame([[], ['id' => 7]], $pdo->executions);
        self::assertSame(2, $pdo->closedCursors);
    }

    public function testIrreversibleDownOperationReturnsGovernedFailure(): void
    {
        $pdo = new RecordingSqlOperationPdo();
        $handler = $this->handler($pdo, $this->operation());

        $result = $handler->execute($this->descriptor(false), MigrationDirection::down());

        self::assertFalse($result->successful());
        self::assertSame('IRREVERSIBLE_MIGRATION', $result->code());
        self::assertSame([], $pdo->preparedSql);
    }

    public function testPdoFailureIsTranslatedAndPreservesCause(): void
    {
        $pdo = new RecordingSqlOperationPdo();
        $pdo->failure = new PDOException('database unavailable');
        $handler = $this->handler($pdo, $this->operation());

        try {
            $handler->execute($this->descriptor(false), MigrationDirection::up());
            self::fail('PDO failure must be translated.');
        } catch (PdoMigrationSqlExecutionException $exception) {
            self::assertSame($pdo->failure, $exception->getPrevious());
        }
    }

    private function operation(): PdoMigrationSqlOperation
    {
        return new PdoMigrationSqlOperation(
            new MigrationId('users.create'),
            [new PdoMigrationSqlStatement('CREATE TABLE users (id INT)')],
        );
    }

    private function descriptor(bool $reversible): MigrationDescriptor
    {
        return new MigrationDescriptor(
            new MigrationId('users.create'),
            new MigrationChecksum('sha256', str_repeat('a', 64)),
            reversible: $reversible,
        );
    }

    private function handler(
        RecordingSqlOperationPdo $pdo,
        PdoMigrationSqlOperation $operation,
    ): PdoMigrationSqlOperationHandler {
        return new PdoMigrationSqlOperationHandler(
            new PdoMigrationConnection(
                $pdo,
                new PdoMigrationConnectionName('primary'),
                PdoMigrationPlatform::postgresql(),
                PdoMigrationConnectionOwnership::external(),
                PdoMigrationCapabilities::postgresql(),
            ),
            new PdoMigrationSqlOperationCatalog([$operation]),
        );
    }
}

final class RecordingSqlOperationPdo extends PDO
{
    /** @var list<string> */
    public array $preparedSql = [];

    /** @var list<array<string, bool|float|int|string|null>> */
    public array $executions = [];

    public int $closedCursors = 0;

    public ?PDOException $failure = null;

    public function __construct()
    {
    }

    /** @param array<mixed> $options */
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }

        $this->preparedSql[] = $query;

        return new RecordingSqlOperationStatement($this);
    }
}

final class RecordingSqlOperationStatement extends PDOStatement
{
    public function __construct(private readonly RecordingSqlOperationPdo $pdo)
    {
    }

    /** @param array<string, bool|float|int|string|null>|null $params */
    public function execute(?array $params = null): bool
    {
        $this->pdo->executions[] = $params ?? [];

        return true;
    }

    public function closeCursor(): bool
    {
        ++$this->pdo->closedCursors;

        return true;
    }
}
