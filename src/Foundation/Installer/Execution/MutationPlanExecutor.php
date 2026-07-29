<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Execution;

use Sif\Foundation\Installer\Contracts\MutationHandlerInterface;
use Sif\Foundation\Installer\Exceptions\AmbiguousMutationHandlerException;
use Sif\Foundation\Installer\Exceptions\InvalidMutationHandlerException;
use Sif\Foundation\Installer\Exceptions\InvalidMutationHandlerResultException;
use Sif\Foundation\Installer\Exceptions\MissingMutationHandlerException;
use Sif\Foundation\Installer\Mutations\MutationDescriptor;
use Sif\Foundation\Installer\Mutations\MutationPlan;
use Throwable;

final readonly class MutationPlanExecutor
{
    /** @var list<MutationHandlerInterface> */
    private array $handlers;

    /** @param iterable<MutationHandlerInterface> $handlers */
    public function __construct(iterable $handlers)
    {
        $normalized = [];
        foreach ($handlers as $handler) {
            if (!$handler instanceof MutationHandlerInterface) {
                throw new InvalidMutationHandlerException('Mutation executors accept only MutationHandlerInterface instances.');
            }
            $normalized[] = $handler;
        }
        $this->handlers = $normalized;
    }

    public function execute(MutationPlan $plan): InstallationExecutionReport
    {
        $entries = [];
        $applied = [];

        foreach ($plan->mutations() as $mutation) {
            $handler = $this->handlerFor($mutation);
            try {
                $result = $handler->apply($mutation);
                $this->assertResult($mutation, $result, MutationExecutionStatus::applied());
                $entries[] = new MutationJournalEntry(count($entries) + 1, $result);
                $applied[] = [$mutation, $handler, $result];
            } catch (Throwable $throwable) {
                $entries[] = new MutationJournalEntry(
                    count($entries) + 1,
                    new MutationExecutionResult(
                        $mutation->identifier(),
                        MutationExecutionStatus::failed(),
                        null,
                        ['failure_type' => $throwable::class],
                    ),
                );

                $rollbackCompleted = $this->rollback($applied, $entries);

                return new InstallationExecutionReport(
                    $plan->fingerprint(),
                    false,
                    new MutationJournal($entries),
                    $mutation->identifier(),
                    $applied !== [],
                    $applied !== [] && $rollbackCompleted,
                );
            }
        }

        return new InstallationExecutionReport(
            $plan->fingerprint(),
            true,
            new MutationJournal($entries),
            null,
            false,
            false,
        );
    }

    /**
     * @param list<array{MutationDescriptor, MutationHandlerInterface, MutationExecutionResult}> $applied
     * @param list<MutationJournalEntry> $entries
     */
    private function rollback(array $applied, array &$entries): bool
    {
        $completed = true;
        foreach (array_reverse($applied) as [$mutation, $handler, $appliedResult]) {
            if (!$mutation->rollbackPolicy()->isSupported()) {
                $entries[] = new MutationJournalEntry(
                    count($entries) + 1,
                    new MutationExecutionResult(
                        $mutation->identifier(),
                        MutationExecutionStatus::compensationUnsupported(),
                    ),
                );
                $completed = false;
                continue;
            }

            try {
                $result = $handler->compensate($mutation, $appliedResult);
                $this->assertResult($mutation, $result, MutationExecutionStatus::compensated());
                $entries[] = new MutationJournalEntry(count($entries) + 1, $result);
            } catch (Throwable $throwable) {
                $entries[] = new MutationJournalEntry(
                    count($entries) + 1,
                    new MutationExecutionResult(
                        $mutation->identifier(),
                        MutationExecutionStatus::compensationFailed(),
                        null,
                        ['failure_type' => $throwable::class],
                    ),
                );
                $completed = false;
            }
        }

        return $completed;
    }

    private function handlerFor(MutationDescriptor $mutation): MutationHandlerInterface
    {
        $matches = [];
        foreach ($this->handlers as $handler) {
            if ($handler->supports($mutation)) {
                $matches[] = $handler;
            }
        }
        if ($matches === []) {
            throw new MissingMutationHandlerException(sprintf('No mutation handler supports "%s".', $mutation->identifier()));
        }
        if (count($matches) > 1) {
            throw new AmbiguousMutationHandlerException(sprintf('Multiple mutation handlers support "%s".', $mutation->identifier()));
        }

        return $matches[0];
    }

    private function assertResult(
        MutationDescriptor $mutation,
        MutationExecutionResult $result,
        MutationExecutionStatus $expectedStatus,
    ): void {
        if (
            $result->mutationIdentifier() !== $mutation->identifier()
            || !$result->status()->equals($expectedStatus)
        ) {
            throw new InvalidMutationHandlerResultException(
                sprintf('Mutation handler returned an invalid result for "%s".', $mutation->identifier()),
            );
        }
    }
}
