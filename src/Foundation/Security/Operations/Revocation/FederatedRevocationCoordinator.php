<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use DateTimeImmutable;
use LogicException;
use Sif\Foundation\Security\Contracts\FederatedRevocationJournalInterface;

final readonly class FederatedRevocationCoordinator
{
    public function __construct(
        private FederatedRevocationOrchestrator $orchestrator,
        private FederatedRevocationJournalInterface $journal,
        private FederatedRevocationIdempotencyGuard $guard
    ) {
    }

    public function execute(
        FederatedRevocationOperationId $operationId,
        FederatedRevocationRequest $request,
        DateTimeImmutable $now
    ): FederatedRevocationExecution {
        $decision = $this->guard->decide($operationId);

        if ($decision === FederatedRevocationRetryDecision::ReuseCompleted) {
            $existing = $this->guard->existing($operationId);

            if ($existing === null) {
                throw new LogicException(
                    'Completed revocation record unexpectedly disappeared.'
                );
            }

            return $existing->execution();
        }

        $execution = $this->orchestrator->execute(
            $request,
            $now
        );

        $this->journal->save(
            new FederatedRevocationExecutionRecord(
                $operationId,
                $execution,
                $now
            )
        );

        return $execution;
    }
}
