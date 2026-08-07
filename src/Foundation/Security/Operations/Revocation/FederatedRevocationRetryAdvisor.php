<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use DateTimeImmutable;

final readonly class FederatedRevocationRetryAdvisor
{
    public function __construct(
        private FederatedRevocationRetryPolicy $policy
    ) {
    }

    public function assess(
        int $previousAttempts,
        DateTimeImmutable $lastAttemptAt
    ): FederatedRevocationRetryAssessment {
        $nextAttempt = $previousAttempts + 1;

        if ($nextAttempt > $this->policy->maxAttempts()) {
            return new FederatedRevocationRetryAssessment(
                false,
                new FederatedRevocationRetryState(
                    $previousAttempts,
                    null
                ),
                'max_attempts_exhausted'
            );
        }

        $nextEligibleAt = $lastAttemptAt->add(
            $this->policy->delayForAttempt($nextAttempt)
        );

        return new FederatedRevocationRetryAssessment(
            true,
            new FederatedRevocationRetryState(
                $previousAttempts,
                $nextEligibleAt
            )
        );
    }
}
