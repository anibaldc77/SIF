<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorization;
use Sif\Foundation\Migration\Contracts\MigrationHistoryStoreInterface;
use Sif\Foundation\Migration\Contracts\MigrationLockInterface;
use Sif\Foundation\Migration\Contracts\MigrationOperationHandlerInterface;
use Sif\Foundation\Migration\Contracts\MigrationTransactionManagerInterface;
use Sif\Foundation\Migration\Exceptions\MigrationExecutionNotAllowedException;
use Sif\Foundation\Migration\Exceptions\MigrationLockUnavailableException;
use Sif\Foundation\Migration\Execution\MigrationExecutor;
use Sif\Foundation\Migration\Execution\MigrationOperationResult;
use Sif\Foundation\Migration\History\MigrationHistory;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\MigrationVersion;
use Sif\Foundation\Migration\Planning\MigrationPlan;
use Sif\Foundation\Migration\Selection\MigrationExecutionPlan;

final class MigrationExecutionCoordinationTest extends TestCase
{
    public function testSuccessfulExecutionCoordinatesLockTransactionAndHistory(): void
    {
        $history = new MemoryHistoryStore();
        $lock = new RecordingLock();
        $transactions = new RecordingTransactions(true);
        $handler = new RecordingHandler(MigrationOperationResult::success());
        $plan = $this->plan(MigrationDirection::up(), MigrationExecutionMode::apply(), ['one', 'two']);

        $report = (new MigrationExecutor([$handler], $history, $lock, $transactions))
            ->execute($plan, $this->authorization($plan));

        self::assertTrue($report->successful());
        self::assertSame(2, $report->completedCount());
        self::assertSame(['begin', 'commit', 'begin', 'commit'], $transactions->events);
        self::assertSame(['one', 'two'], $history->history()->identifiers());
        self::assertSame(1, $lock->acquires);
        self::assertSame(1, $lock->releases);
    }

    public function testFailedOperationRollsBackAndStopsRemainingMigrations(): void
    {
        $history = new MemoryHistoryStore();
        $transactions = new RecordingTransactions(true);
        $handler = new RecordingHandler(MigrationOperationResult::failure('DDL_FAILED'));
        $plan = $this->plan(MigrationDirection::up(), MigrationExecutionMode::apply(), ['one', 'two']);

        $report = (new MigrationExecutor([$handler], $history, new RecordingLock(), $transactions))
            ->execute($plan, $this->authorization($plan));

        self::assertFalse($report->successful());
        self::assertSame(0, $report->completedCount());
        self::assertSame(['begin', 'rollback'], $transactions->events);
        self::assertSame([], $history->history()->identifiers());
        self::assertSame(1, $handler->calls);
        self::assertSame('DDL_FAILED', $report->entries()[0]->code());
    }

    public function testHandlerExceptionIsSanitizedAndLockIsReleased(): void
    {
        $handler = new RecordingHandler(MigrationOperationResult::success(), true);
        $lock = new RecordingLock();
        $plan = $this->plan(MigrationDirection::up(), MigrationExecutionMode::apply(), ['one']);

        $report = (new MigrationExecutor([$handler], new MemoryHistoryStore(), $lock, new RecordingTransactions(true)))
            ->execute($plan, $this->authorization($plan));

        self::assertSame('EXECUTION_EXCEPTION', $report->entries()[0]->code());
        self::assertSame(1, $lock->releases);
        self::assertStringNotContainsString('secret', json_encode($report->summary(), JSON_THROW_ON_ERROR));
    }

    public function testRollbackExecutionRecordsRolledBackStatus(): void
    {
        $history = new MemoryHistoryStore();
        $plan = $this->plan(MigrationDirection::down(), MigrationExecutionMode::apply(), ['one']);

        (new MigrationExecutor(
            [new RecordingHandler(MigrationOperationResult::success())],
            $history,
            new RecordingLock(),
            new RecordingTransactions(false),
        ))->execute($plan, $this->authorization($plan));

        self::assertSame('rolled_back', $history->find(new MigrationId('one'))?->status()->value());
    }

    public function testUnavailableLockRejectsExecutionBeforeHandlerInvocation(): void
    {
        $handler = new RecordingHandler(MigrationOperationResult::success());
        $lock = new RecordingLock(false);
        $plan = $this->plan(MigrationDirection::up(), MigrationExecutionMode::apply(), ['one']);

        $this->expectException(MigrationLockUnavailableException::class);
        try {
            (new MigrationExecutor([$handler], new MemoryHistoryStore(), $lock, new RecordingTransactions(true)))
                ->execute($plan, $this->authorization($plan));
        } finally {
            self::assertSame(0, $handler->calls);
            self::assertSame(0, $lock->releases);
        }
    }

    public function testDryRunPlanCannotExecute(): void
    {
        $plan = $this->plan(MigrationDirection::up(), MigrationExecutionMode::dryRun(), ['one']);

        $this->expectException(MigrationExecutionNotAllowedException::class);
        (new MigrationExecutor(
            [new RecordingHandler(MigrationOperationResult::success())],
            new MemoryHistoryStore(),
            new RecordingLock(),
            new RecordingTransactions(true),
        ))->execute($plan, $this->authorization($plan));
    }

    public function testNonTransactionalProviderDoesNotInvokeTransactionMethods(): void
    {
        $transactions = new RecordingTransactions(false);
        $plan = $this->plan(MigrationDirection::up(), MigrationExecutionMode::apply(), ['one']);

        $report = (new MigrationExecutor(
            [new RecordingHandler(MigrationOperationResult::success())],
            new MemoryHistoryStore(),
            new RecordingLock(),
            $transactions,
        ))->execute($plan, $this->authorization($plan));

        self::assertTrue($report->successful());
        self::assertSame([], $transactions->events);
    }

    /** @param list<string> $ids */
    private function plan(MigrationDirection $direction, MigrationExecutionMode $mode, array $ids): MigrationExecutionPlan
    {
        $descriptors = array_map(
            static fn (string $id): MigrationDescriptor => new MigrationDescriptor(
                new MigrationId($id),
                MigrationChecksum::sha256($id),
                new MigrationVersion('1.0.0'),
                reversible: true,
            ),
            $ids,
        );

        return new MigrationExecutionPlan(
            new MigrationRequest($direction, $mode),
            new MigrationPlan($direction, $descriptors),
        );
    }

    private function authorization(MigrationExecutionPlan $plan): MigrationExecutionAuthorization
    {
        return new MigrationExecutionAuthorization(
            'batch-20260730',
            $plan->fingerprint(),
            $plan->direction(),
            $plan->mode(),
            true,
        );
    }
}

final class RecordingHandler implements MigrationOperationHandlerInterface
{
    public int $calls = 0;

    public function __construct(
        private readonly MigrationOperationResult $result,
        private readonly bool $throws = false,
    ) {
    }

    public function supports(MigrationDescriptor $migration): bool
    {
        return true;
    }

    public function execute(MigrationDescriptor $migration, MigrationDirection $direction): MigrationOperationResult
    {
        ++$this->calls;
        if ($this->throws) {
            throw new \RuntimeException('secret database exception');
        }
        return $this->result;
    }
}

final class RecordingLock implements MigrationLockInterface
{
    public int $acquires = 0;
    public int $releases = 0;

    public function __construct(private readonly bool $available = true)
    {
    }

    public function acquire(string $owner): bool
    {
        ++$this->acquires;
        return $this->available;
    }

    public function release(string $owner): void
    {
        ++$this->releases;
    }
}

final class RecordingTransactions implements MigrationTransactionManagerInterface
{
    /** @var list<string> */
    public array $events = [];

    public function __construct(private readonly bool $supported)
    {
    }

    public function supportsTransactions(): bool
    {
        return $this->supported;
    }

    public function begin(): void
    {
        $this->events[] = 'begin';
    }

    public function commit(): void
    {
        $this->events[] = 'commit';
    }

    public function rollBack(): void
    {
        $this->events[] = 'rollback';
    }
}

final class MemoryHistoryStore implements MigrationHistoryStoreInterface
{
    /** @var array<string, MigrationHistoryRecord> */
    private array $records = [];

    public function history(): MigrationHistory
    {
        return new MigrationHistory(array_values($this->records));
    }

    public function find(MigrationId $id): ?MigrationHistoryRecord
    {
        return $this->records[$id->value()] ?? null;
    }

    public function append(MigrationHistoryRecord $record): void
    {
        $this->records[$record->id()->value()] = $record;
    }

    public function remove(MigrationId $id): void
    {
        unset($this->records[$id->value()]);
    }
}
