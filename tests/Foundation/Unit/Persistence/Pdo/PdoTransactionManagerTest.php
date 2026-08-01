<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence\Pdo;

use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnection;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnectionOwnership;
use Sif\Foundation\Persistence\Pdo\Exception\PdoTransactionException;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistenceCapabilities;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;
use Sif\Foundation\Persistence\Pdo\Transaction\PdoExternalTransactionPolicy;
use Sif\Foundation\Persistence\Pdo\Transaction\PdoTransactionManager;
use Sif\Foundation\Persistence\Pdo\Transaction\PdoTransactionScope;
use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\TransactionState;

final class PdoTransactionManagerTest extends TestCase
{
    public function testOwnTransactionCommitsAndReturnsResult(): void
    {
        $pdo = new RecordingTransactionPdo();
        $manager = $this->manager($pdo);

        self::assertSame('done', $manager->transactional(static fn (): string => 'done'));
        self::assertSame(['begin', 'commit'], $pdo->events);
        self::assertSame(TransactionState::Committed, $manager->state());
        self::assertSame(0, $manager->depth());
        self::assertSame(PdoTransactionScope::None, $manager->scope());
    }

    public function testFailureRollsBackAndRethrowsOriginalFailure(): void
    {
        $pdo = new RecordingTransactionPdo();
        $manager = $this->manager($pdo);
        $failure = new RuntimeException('boom');

        $actual = null;
        try {
            $manager->transactional(static function () use ($failure): void { throw $failure; });
        } catch (RuntimeException $caught) {
            $actual = $caught;
        }
        self::assertSame($failure, $actual);

        self::assertSame(['begin', 'rollback'], $pdo->events);
        self::assertSame(TransactionState::RolledBack, $manager->state());
    }

    public function testExternalTransactionIsRejectedByDefault(): void
    {
        $pdo = new RecordingTransactionPdo(true);
        $manager = $this->manager($pdo);

        $this->expectException(PdoTransactionException::class);
        $manager->transactional(static fn (): null => null);
    }

    public function testExternalTransactionUsesSavepointWhenAuthorized(): void
    {
        $pdo = new RecordingTransactionPdo(true);
        $manager = $this->manager($pdo, PdoExternalTransactionPolicy::Savepoint);

        self::assertSame(42, $manager->transactional(static fn (): int => 42));
        self::assertSame(['exec:SAVEPOINT sif_persistence_scope', 'exec:RELEASE SAVEPOINT sif_persistence_scope'], $pdo->events);
        self::assertTrue($pdo->inTransaction());
    }

    public function testExternalFailureRollsBackOnlyToSavepoint(): void
    {
        $pdo = new RecordingTransactionPdo(true);
        $manager = $this->manager($pdo, PdoExternalTransactionPolicy::Savepoint);

        try {
            $manager->transactional(static function (): never { throw new RuntimeException('fail'); });
        } catch (RuntimeException) {
        }

        self::assertSame(['exec:SAVEPOINT sif_persistence_scope', 'exec:ROLLBACK TO SAVEPOINT sif_persistence_scope'], $pdo->events);
        self::assertTrue($pdo->inTransaction());
    }

    public function testCapabilitiesExposeTransactionsAndSavepoints(): void
    {
        $manager = $this->manager(new RecordingTransactionPdo());

        self::assertTrue($manager->capabilities()->supports(PersistenceCapability::Transactions));
        self::assertTrue($manager->capabilities()->supports(PersistenceCapability::Savepoints));
    }

    private function manager(RecordingTransactionPdo $pdo, PdoExternalTransactionPolicy $policy = PdoExternalTransactionPolicy::Reject): PdoTransactionManager
    {
        return new PdoTransactionManager(new PdoPersistenceConnection(
            $pdo,
            new ConnectionName('default'),
            PdoPersistencePlatform::postgresql(),
            PdoPersistenceConnectionOwnership::external(),
            PdoPersistenceCapabilities::postgresql(),
        ), $policy);
    }
}

final class RecordingTransactionPdo extends PDO
{
    /** @var list<string> */
    public array $events = [];
    private bool $active;

    public function __construct(bool $active = false)
    {
        $this->active = $active;
    }

    public function beginTransaction(): bool
    {
        $this->events[] = 'begin';
        $this->active = true;
        return true;
    }

    public function commit(): bool
    {
        $this->events[] = 'commit';
        $this->active = false;
        return true;
    }

    public function rollBack(): bool
    {
        $this->events[] = 'rollback';
        $this->active = false;
        return true;
    }

    public function inTransaction(): bool
    {
        return $this->active;
    }

    public function exec(string $statement): int|false
    {
        $this->events[] = 'exec:' . $statement;
        return 0;
    }
}
