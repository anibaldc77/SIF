<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use DateTimeImmutable;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationAssessment;

final readonly class FederatedRemoteRetryBridge
{
    public function __construct(
        private FederatedRevocationRetryAdvisor $retryAdvisor
    ) {
    }

    public function assess(
        FederatedProviderRevocationAssessment $remoteAssessment,
        int $previousAttempts,
        DateTimeImmutable $lastAttemptAt
    ): FederatedRevocationRetryAssessment {
        if ($remoteAssessment->terminal()) {
            return new FederatedRevocationRetryAssessment(
                false,
                new FederatedRevocationRetryState(
                    $previousAttempts,
                    null
                ),
                'remote_failure_terminal'
            );
        }

        if (!$remoteAssessment->retryable()) {
            return new FederatedRevocationRetryAssessment(
                false,
                new FederatedRevocationRetryState(
                    $previousAttempts,
                    null
                ),
                'remote_failure_not_retryable'
            );
        }

        return $this->retryAdvisor->assess(
            $previousAttempts,
            $lastAttemptAt
        );
    }
}
