<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence\Pdo;

use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\Pdo\Compilation\PdoCompiledQuery;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnection;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnectionOwnership;
use Sif\Foundation\Persistence\Pdo\Exception\PdoStatementExecutionException;
use Sif\Foundation\Persistence\Pdo\Execution\PdoPreparedStatementExecutor;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistenceCapabilities;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameter;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final class PdoPreparedStatementExecutionTest extends TestCase
{
    public function testExecutesCompiledQueryBindsParametersAndAdaptsRows(): void
    {
        $statement = new RecordingPersistenceStatement([
            ['id' => 1, 'name' => 'Ada'],
            ['id' => 2, 'name' => 'Grace'],
        ], 2);
        $executor = new PdoPreparedStatementExecutor($this->connection(new RecordingPersistencePdo($statement)));
        $query = new PdoCompiledQuery(
            'SELECT "id", "name" FROM "users" WHERE "active" = :p_active_0',
            new PdoSqlParameterBag([new PdoSqlParameter('p_active_0', true)]),
        );

        $result = $executor->execute($query);

        self::assertSame(2, $result->records()->count());
        self::assertSame('Ada', $result->records()->first()?->get('name'));
        self::assertSame(2, $result->affectedRows());
        self::assertSame([':p_active_0' => [true, PDO::PARAM_BOOL]], $statement->bindings);
        self::assertTrue($statement->executed);
        self::assertTrue($statement->closed);
        self::assertSame($query->sql(), $statement->preparedSql);
    }

    public function testResultSummaryDoesNotExposeRowValuesOrParameters(): void
    {
        $statement = new RecordingPersistenceStatement([['secret' => 'classified']], 1);
        $executor = new PdoPreparedStatementExecutor($this->connection(new RecordingPersistencePdo($statement)));
        $result = $executor->execute(new PdoCompiledQuery('SELECT "secret" FROM "records"', new PdoSqlParameterBag()));

        self::assertSame(['record_count' => 1, 'affected_rows' => 1], $result->summary());
        self::assertStringNotContainsString('classified', json_encode($result->summary(), JSON_THROW_ON_ERROR));
    }

    public function testPreparationFailureIsTranslatedAndPreservesCause(): void
    {
        $cause = new PDOException('driver details');
        $pdo = new RecordingPersistencePdo(null, $cause);
        $executor = new PdoPreparedStatementExecutor($this->connection($pdo));

        try {
            $executor->execute(new PdoCompiledQuery('SELECT 1', new PdoSqlParameterBag()));
            self::fail('Expected preparation failure.');
        } catch (PdoStatementExecutionException $exception) {
            self::assertSame('PDO statement preparation failed.', $exception->getMessage());
            self::assertSame($cause, $exception->getPrevious());
            self::assertStringNotContainsString('driver details', $exception->getMessage());
        }
    }

    public function testExecutionFailureClosesCursorAndPreservesCause(): void
    {
        $cause = new PDOException('database details');
        $statement = new RecordingPersistenceStatement([], 0, $cause);
        $executor = new PdoPreparedStatementExecutor($this->connection(new RecordingPersistencePdo($statement)));

        try {
            $executor->execute(new PdoCompiledQuery('SELECT 1', new PdoSqlParameterBag()));
            self::fail('Expected execution failure.');
        } catch (PdoStatementExecutionException $exception) {
            self::assertSame('PDO statement execution failed.', $exception->getMessage());
            self::assertSame($cause, $exception->getPrevious());
            self::assertTrue($statement->closed);
        }
    }

    public function testBindFailureStopsExecution(): void
    {
        $statement = new RecordingPersistenceStatement([], 0, null, false);
        $executor = new PdoPreparedStatementExecutor($this->connection(new RecordingPersistencePdo($statement)));
        $query = new PdoCompiledQuery('SELECT :value', new PdoSqlParameterBag([new PdoSqlParameter('value', 10)]));

        $this->expectException(PdoStatementExecutionException::class);
        $this->expectExceptionMessage('PDO statement execution failed.');

        try {
            $executor->execute($query);
        } finally {
            self::assertFalse($statement->executed);
            self::assertTrue($statement->closed);
        }
    }

    private function connection(PDO $pdo): PdoPersistenceConnection
    {
        $platform = PdoPersistencePlatform::postgresql();

        return new PdoPersistenceConnection(
            $pdo,
            new ConnectionName('primary'),
            $platform,
            PdoPersistenceConnectionOwnership::external(),
            PdoPersistenceCapabilities::postgresql(),
        );
    }
}

final class RecordingPersistencePdo extends PDO
{
    public function __construct(
        private readonly ?RecordingPersistenceStatement $statement,
        private readonly ?PDOException $preparationFailure = null,
    ) {
    }

    /** @param array<int|string, mixed> $options */
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if ($this->preparationFailure instanceof PDOException) {
            throw $this->preparationFailure;
        }
        if (!$this->statement instanceof RecordingPersistenceStatement) {
            return false;
        }
        $this->statement->preparedSql = $query;
        return $this->statement;
    }
}

final class RecordingPersistenceStatement extends PDOStatement
{
    /** @var array<string, array{0: mixed, 1: int}> */
    public array $bindings = [];
    public bool $executed = false;
    public bool $closed = false;
    public string $preparedSql = '';

    /** @param list<array<string, mixed>> $rows */
    public function __construct(
        private readonly array $rows,
        private readonly int $affectedRows,
        private readonly ?PDOException $executionFailure = null,
        private readonly bool $bindResult = true,
    ) {
    }

    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->bindings[(string) $param] = [$value, $type];
        return $this->bindResult;
    }

    /** @param array<int|string, mixed>|null $params */
    public function execute(?array $params = null): bool
    {
        if ($this->executionFailure instanceof PDOException) {
            throw $this->executionFailure;
        }
        $this->executed = true;
        return true;
    }

    /** @return list<array<string, mixed>> */
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->rows;
    }

    public function rowCount(): int
    {
        return $this->affectedRows;
    }

    public function closeCursor(): bool
    {
        $this->closed = true;
        return true;
    }
}
