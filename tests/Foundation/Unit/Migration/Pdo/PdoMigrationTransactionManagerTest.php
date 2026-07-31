<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration\Pdo;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionName;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionOwnership;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationTransactionPolicyException;
use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationTransactionException;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationCapabilities;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;
use Sif\Foundation\Migration\Pdo\Transaction\PdoMigrationExternalTransactionPolicy;
use Sif\Foundation\Migration\Pdo\Transaction\PdoMigrationTransactionManager;

final class PdoMigrationTransactionManagerTest extends TestCase
{
    public function testOwnedTransactionBeginsAndCommits(): void
    {
        $pdo = new RecordingTransactionPdo();
        $manager = $this->manager($pdo);

        $manager->begin();
        self::assertTrue($manager->active());
        self::assertTrue($manager->ownsTransaction());

        $manager->commit();
        self::assertFalse($manager->active());
        self::assertSame(1, $pdo->begins);
        self::assertSame(1, $pdo->commits);
        self::assertSame(0, $pdo->rollbacks);
    }

    public function testOwnedTransactionRollsBack(): void
    {
        $pdo = new RecordingTransactionPdo();
        $manager = $this->manager($pdo);

        $manager->begin();
        $manager->rollBack();

        self::assertFalse($manager->active());
        self::assertSame(1, $pdo->rollbacks);
    }

    public function testExternalTransactionIsRejectedByDefault(): void
    {
        $pdo = new RecordingTransactionPdo(true);
        $manager = $this->manager($pdo);

        $this->expectException(PdoMigrationTransactionException::class);
        $manager->begin();
    }

    public function testSavepointPolicyNeverCommitsExternalTransaction(): void
    {
        $pdo = new RecordingTransactionPdo(true);
        $manager = $this->manager($pdo, PdoMigrationExternalTransactionPolicy::savepoint());

        $manager->begin();
        self::assertTrue($manager->usesSavepoint());
        $manager->commit();

        self::assertSame(['SAVEPOINT sif_migration', 'RELEASE SAVEPOINT sif_migration'], $pdo->executed);
        self::assertSame(0, $pdo->commits);
        self::assertTrue($pdo->inTransaction());
    }

    public function testSavepointRollbackPreservesExternalTransaction(): void
    {
        $pdo = new RecordingTransactionPdo(true);
        $manager = $this->manager($pdo, PdoMigrationExternalTransactionPolicy::savepoint());

        $manager->begin();
        $manager->rollBack();

        self::assertSame(
            ['SAVEPOINT sif_migration', 'ROLLBACK TO SAVEPOINT sif_migration', 'RELEASE SAVEPOINT sif_migration'],
            $pdo->executed,
        );
        self::assertSame(0, $pdo->rollbacks);
        self::assertTrue($pdo->inTransaction());
    }

    public function testNestedManagerBeginIsRejected(): void
    {
        $pdo = new RecordingTransactionPdo();
        $manager = $this->manager($pdo);
        $manager->begin();

        $this->expectException(PdoMigrationTransactionException::class);
        $manager->begin();
    }

    public function testStateDivergenceFailsClosed(): void
    {
        $pdo = new RecordingTransactionPdo();
        $manager = $this->manager($pdo);
        $manager->begin();
        $pdo->active = false;

        $this->expectException(PdoMigrationTransactionException::class);
        $manager->commit();
    }

    public function testUnsupportedTransactionsFailClosed(): void
    {
        $pdo = new RecordingTransactionPdo();
        $capabilities = new PdoMigrationCapabilities(
            PdoMigrationPlatform::mysql(),
            false,
            false,
            false,
            false,
            'named-lock',
            'session',
            true,
            [MigrationDirection::up()],
        );
        $manager = new PdoMigrationTransactionManager($this->connection($pdo, $capabilities));

        self::assertFalse($manager->supportsTransactions());
        $this->expectException(PdoMigrationTransactionException::class);
        $manager->begin();
    }

    public function testPolicyAndSavepointIdentifierAreValidated(): void
    {
        try {
            new PdoMigrationExternalTransactionPolicy('join');
            self::fail('Unknown policy must be rejected.');
        } catch (InvalidPdoMigrationTransactionPolicyException) {
            self::assertTrue(true);
        }

        $this->expectException(PdoMigrationTransactionException::class);
        new PdoMigrationTransactionManager($this->connection(new RecordingTransactionPdo()), savepoint: 'bad-name');
    }

    public function testPdoFailurePreservesCause(): void
    {
        $pdo = new RecordingTransactionPdo();
        $pdo->beginFailure = new PDOException('boom');
        $manager = $this->manager($pdo);

        try {
            $manager->begin();
            self::fail('PDO failure must be translated.');
        } catch (PdoMigrationTransactionException $exception) {
            self::assertSame($pdo->beginFailure, $exception->getPrevious());
        }
    }

    private function manager(
        RecordingTransactionPdo $pdo,
        ?PdoMigrationExternalTransactionPolicy $policy = null,
    ): PdoMigrationTransactionManager {
        return new PdoMigrationTransactionManager(
            $this->connection($pdo),
            $policy ?? PdoMigrationExternalTransactionPolicy::reject(),
        );
    }

    private function connection(
        RecordingTransactionPdo $pdo,
        ?PdoMigrationCapabilities $capabilities = null,
    ): PdoMigrationConnection {
        return new PdoMigrationConnection(
            $pdo,
            new PdoMigrationConnectionName('primary'),
            PdoMigrationPlatform::mysql(),
            PdoMigrationConnectionOwnership::external(),
            $capabilities ?? PdoMigrationCapabilities::mysql(),
        );
    }
}

final class RecordingTransactionPdo extends PDO
{
    public bool $active;
    public int $begins = 0;
    public int $commits = 0;
    public int $rollbacks = 0;
    /** @var list<string> */
    public array $executed = [];
    public ?PDOException $beginFailure = null;

    public function __construct(bool $active = false)
    {
        $this->active = $active;
    }

    public function inTransaction(): bool
    {
        return $this->active;
    }

    public function beginTransaction(): bool
    {
        if ($this->beginFailure !== null) {
            throw $this->beginFailure;
        }

        ++$this->begins;
        $this->active = true;
        return true;
    }

    public function commit(): bool
    {
        ++$this->commits;
        $this->active = false;
        return true;
    }

    public function rollBack(): bool
    {
        ++$this->rollbacks;
        $this->active = false;
        return true;
    }

    public function exec(string $statement): int|false
    {
        $this->executed[] = $statement;
        return 0;
    }
}
