<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence\Pdo;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\MapperInterface;
use Sif\Foundation\Contracts\QueryInterface;
use Sif\Foundation\Contracts\ResultSetInterface;
use Sif\Foundation\Contracts\TransactionManagerInterface;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\Pdo\Compilation\PostgreSqlSelectQueryCompiler;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnection;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnectionOwnership;
use Sif\Foundation\Persistence\Pdo\Execution\PdoPreparedStatementExecutor;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistenceCapabilities;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;
use Sif\Foundation\Persistence\Pdo\Repository\PdoManagedRepository;
use Sif\Foundation\Persistence\Pdo\Repository\PdoRecordKey;
use Sif\Foundation\Persistence\Pdo\Repository\PdoRepository;
use Sif\Foundation\Persistence\Pdo\Repository\PdoRepositoryDefinition;
use Sif\Foundation\Persistence\Pdo\Repository\PdoRepositoryRegistry;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Translation\PdoQueryTranslator;
use Sif\Foundation\Persistence\Pdo\UnitOfWork\PdoUnitOfWork;
use Sif\Foundation\Persistence\PersistenceCapabilities;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\ResultSet;
use Sif\Foundation\Persistence\StorageRecord;
use Sif\Foundation\Persistence\TransactionState;

final class PdoRepositoryUnitOfWorkTest extends TestCase
{
    public function testRecordKeyIsDeterministicAndSafe(): void
    {
        $key = new PdoRecordKey(['tenant_id' => 7, 'id' => 3]);

        self::assertSame(['id' => 3, 'tenant_id' => 7], $key->values());
        self::assertSame(['columns' => ['id', 'tenant_id'], 'count' => 2], $key->summary());
    }

    public function testRepositoryQueriesAndInsertsWithPreparedStatements(): void
    {
        $select = new RecordingRepositoryStatement([], 0);
        $insert = new RecordingRepositoryStatement([], 1);
        $pdo = new SequencedRepositoryPdo([$select, $insert]);
        $platform = PdoPersistencePlatform::postgresql();
        $repository = $this->repository($pdo, $platform);
        $entity = new RepositoryFixture(10, 'Ada');

        $repository->save($entity);

        self::assertStringContainsString('SELECT * FROM "people"', $select->preparedSql);
        self::assertStringContainsString('INSERT INTO "people"', $insert->preparedSql);
        self::assertSame([10, PDO::PARAM_INT], $insert->bindings[':write_id']);
        self::assertSame(['Ada', PDO::PARAM_STR], $insert->bindings[':write_name']);
    }

    public function testCompositeKeyLookupHydratesManagedObject(): void
    {
        $statement = new RecordingRepositoryStatement([['id' => 10, 'name' => 'Ada']], 1);
        $platform = PdoPersistencePlatform::postgresql();
        $repository = $this->repository(new SequencedRepositoryPdo([$statement]), $platform);

        $result = $repository->findByKey(new PdoRecordKey(['id' => 10]));

        self::assertInstanceOf(RepositoryFixture::class, $result);
        self::assertSame('Ada', $result->name);
        self::assertStringContainsString('WHERE "id" = :p_id_0', $statement->preparedSql);
    }

    public function testUnitOfWorkRoutesChangesInsideOneTransaction(): void
    {
        $repository = new RecordingWriteRepository();
        $transactions = new RecordingTransactionManager();
        $unit = new PdoUnitOfWork($transactions, new PdoRepositoryRegistry([$repository]));
        $new = new RepositoryFixture(1, 'new');
        $dirty = new RepositoryFixture(2, 'dirty');
        $removed = new RepositoryFixture(3, 'removed');

        $unit->registerNew($new);
        $unit->registerDirty($dirty);
        $unit->registerRemoved($removed);
        $unit->commit();

        self::assertSame(1, $transactions->calls);
        self::assertSame([1, 2], $repository->saved);
        self::assertSame([3], $repository->removed);
        self::assertTrue($unit->isEmpty());
    }

    /** @return PdoRepository<RepositoryFixture> */
    private function repository(PDO $pdo, PdoPersistencePlatform $platform): PdoRepository
    {
        $connection = new PdoPersistenceConnection(
            $pdo,
            new ConnectionName('primary'),
            $platform,
            PdoPersistenceConnectionOwnership::external(),
            PdoPersistenceCapabilities::postgresql(),
        );
        $definition = new PdoRepositoryDefinition(
            new RepositoryName('people'),
            RepositoryFixture::class,
            new PdoSqlIdentifier('people'),
            new RepositoryFixtureMapper(),
            static fn (RepositoryFixture $entity): PdoRecordKey => PdoRecordKey::single('id', $entity->id),
            ['id'],
            ['id', 'name'],
        );

        return new PdoRepository(
            $definition,
            new PdoQueryTranslator(),
            new PostgreSqlSelectQueryCompiler($platform),
            new PdoPreparedStatementExecutor($connection),
            $platform,
            PersistenceCapabilities::none(),
        );
    }
}

final readonly class RepositoryFixture
{
    public function __construct(public int $id, public string $name) {}
}

/** @implements MapperInterface<RepositoryFixture> */
final class RepositoryFixtureMapper implements MapperInterface
{
    public function hydrate(StorageRecord $record): object
    {
        return new RepositoryFixture((int) $record->get('id'), (string) $record->get('name'));
    }

    public function extract(object $object): StorageRecord
    {
        if (!$object instanceof RepositoryFixture) {
            throw new \InvalidArgumentException('Unexpected fixture type.');
        }
        return new StorageRecord(['id' => $object->id, 'name' => $object->name]);
    }
}

final class SequencedRepositoryPdo extends PDO
{
    /** @param list<RecordingRepositoryStatement> $statements */
    public function __construct(private array $statements) {}

    /** @param array<int|string, mixed> $options */
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $statement = array_shift($this->statements);
        if (!$statement instanceof RecordingRepositoryStatement) {
            return false;
        }
        $statement->preparedSql = $query;
        return $statement;
    }
}

final class RecordingRepositoryStatement extends PDOStatement
{
    /** @var array<string, array{0: mixed, 1: int}> */
    public array $bindings = [];
    public string $preparedSql = '';

    /** @param list<array<string, mixed>> $rows */
    public function __construct(private array $rows, private int $affectedRows) {}

    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->bindings[(string) $param] = [$value, $type];
        return true;
    }

    /** @param array<int|string, mixed>|null $params */
    public function execute(?array $params = null): bool { return true; }
    /** @return list<array<string, mixed>> */
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return $this->rows; }
    public function rowCount(): int { return $this->affectedRows; }
    public function closeCursor(): bool { return true; }
}

final class RecordingWriteRepository implements PdoManagedRepository
{
    /** @var list<int> */ public array $saved = [];
    /** @var list<int> */ public array $removed = [];
    public function managedType(): string { return RepositoryFixture::class; }
    public function supports(object $object): bool { return $object instanceof RepositoryFixture; }
    public function saveObject(object $object): void { if ($object instanceof RepositoryFixture) { $this->saved[] = $object->id; } }
    public function removeObject(object $object): void { if ($object instanceof RepositoryFixture) { $this->removed[] = $object->id; } }
}

final class RecordingTransactionManager implements TransactionManagerInterface
{
    public int $calls = 0;
    public function transactional(callable $operation): mixed { ++$this->calls; return $operation(); }
    public function state(): TransactionState { return TransactionState::Committed; }
    public function depth(): int { return 0; }
}
