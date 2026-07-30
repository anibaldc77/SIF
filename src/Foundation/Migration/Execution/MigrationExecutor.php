<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Execution;

use DateTimeImmutable;
use Throwable;
use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorization;
use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorizer;
use Sif\Foundation\Migration\Contracts\MigrationHistoryStoreInterface;
use Sif\Foundation\Migration\Contracts\MigrationLockInterface;
use Sif\Foundation\Migration\Contracts\MigrationOperationHandlerInterface;
use Sif\Foundation\Migration\Contracts\MigrationTransactionManagerInterface;
use Sif\Foundation\Migration\Exceptions\AmbiguousMigrationOperationHandlerException;
use Sif\Foundation\Migration\Exceptions\MigrationExecutionNotAllowedException;
use Sif\Foundation\Migration\Exceptions\MigrationLockUnavailableException;
use Sif\Foundation\Migration\Exceptions\MissingMigrationOperationHandlerException;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\History\MigrationHistoryStatus;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\Selection\MigrationExecutionPlan;

final class MigrationExecutor
{
    /** @var list<MigrationOperationHandlerInterface> */
    private array $handlers;

    /** @param iterable<MigrationOperationHandlerInterface> $handlers */
    public function __construct(
        iterable $handlers,
        private readonly MigrationHistoryStoreInterface $historyStore,
        private readonly MigrationLockInterface $lock,
        private readonly MigrationTransactionManagerInterface $transactions,
        private readonly MigrationExecutionAuthorizer $authorizer = new MigrationExecutionAuthorizer(),
    ) {
        $normalized = [];
        foreach ($handlers as $handler) {
            if (!$handler instanceof MigrationOperationHandlerInterface) {
                throw new MissingMigrationOperationHandlerException(
                    'Migration handlers must implement MigrationOperationHandlerInterface.',
                );
            }
            $normalized[] = $handler;
        }
        $this->handlers = $normalized;
    }

    public function execute(
        MigrationExecutionPlan $plan,
        MigrationExecutionAuthorization $authorization,
    ): MigrationExecutionReport {
        if (!$plan->mode()->mutatesState()) {
            throw new MigrationExecutionNotAllowedException('Dry-run migration plans cannot be executed.');
        }

        $this->authorizer->assertAuthorized($plan, $authorization);
        $owner = 'migration:' . $authorization->authorizationId();
        if (!$this->lock->acquire($owner)) {
            throw new MigrationLockUnavailableException('Migration execution lock is unavailable.');
        }

        $entries = [];
        try {
            foreach ($plan->migrations() as $descriptor) {
                $sequence = count($entries) + 1;
                $handler = $this->handlerFor($descriptor);
                $transactionStarted = false;

                try {
                    if ($this->transactions->supportsTransactions()) {
                        $this->transactions->begin();
                        $transactionStarted = true;
                    }

                    $result = $handler->execute($descriptor, $plan->direction());
                    if (!$result->successful()) {
                        if ($transactionStarted) {
                            $this->transactions->rollBack();
                        }
                        $entries[] = new MigrationExecutionEntry(
                            $sequence,
                            $descriptor->id(),
                            false,
                            $result->code(),
                        );
                        break;
                    }

                    $this->historyStore->append($this->historyRecord($descriptor, $plan, $authorization));
                    if ($transactionStarted) {
                        $this->transactions->commit();
                    }
                    $entries[] = new MigrationExecutionEntry($sequence, $descriptor->id(), true);
                } catch (Throwable) {
                    if ($transactionStarted) {
                        try {
                            $this->transactions->rollBack();
                        } catch (Throwable) {
                        }
                    }
                    $entries[] = new MigrationExecutionEntry(
                        $sequence,
                        $descriptor->id(),
                        false,
                        'EXECUTION_EXCEPTION',
                    );
                    break;
                }
            }
        } finally {
            $this->lock->release($owner);
        }

        return new MigrationExecutionReport($plan->fingerprint(), $plan->direction(), $entries);
    }

    private function handlerFor(MigrationDescriptor $descriptor): MigrationOperationHandlerInterface
    {
        $matches = [];
        foreach ($this->handlers as $handler) {
            if ($handler->supports($descriptor)) {
                $matches[] = $handler;
            }
        }
        if ($matches === []) {
            throw new MissingMigrationOperationHandlerException(
                sprintf('No migration operation handler supports "%s".', $descriptor->id()->value()),
            );
        }
        if (count($matches) > 1) {
            throw new AmbiguousMigrationOperationHandlerException(
                sprintf('Multiple migration operation handlers support "%s".', $descriptor->id()->value()),
            );
        }
        return $matches[0];
    }

    private function historyRecord(
        MigrationDescriptor $descriptor,
        MigrationExecutionPlan $plan,
        MigrationExecutionAuthorization $authorization,
    ): MigrationHistoryRecord {
        return new MigrationHistoryRecord(
            $descriptor->id(),
            $descriptor->checksum(),
            $plan->direction()->isUp()
                ? MigrationHistoryStatus::applied()
                : MigrationHistoryStatus::rolledBack(),
            new DateTimeImmutable('now'),
            $descriptor->version(),
            $authorization->authorizationId(),
        );
    }
}
