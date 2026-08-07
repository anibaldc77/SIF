<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use Sif\Foundation\Security\Contracts\FederatedRevocationJournalInterface;

final readonly class FederatedRevocationIdempotencyGuard
{
    public function __construct(
        private FederatedRevocationJournalInterface $journal
    ) {
    }

    public function decide(
        FederatedRevocationOperationId $operationId
    ): FederatedRevocationRetryDecision {
        $existing = $this->journal->find($operationId);

        if ($existing === null) {
            return FederatedRevocationRetryDecision::Execute;
        }

        if ($existing->completed()) {
            return FederatedRevocationRetryDecision::ReuseCompleted;
        }

        return FederatedRevocationRetryDecision::RetryIncomplete;
    }

    public function existing(
        FederatedRevocationOperationId $operationId
    ): ?FederatedRevocationExecutionRecord {
        return $this->journal->find($operationId);
    }
}
