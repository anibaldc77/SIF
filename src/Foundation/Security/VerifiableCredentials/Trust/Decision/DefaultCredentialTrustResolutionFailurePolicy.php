<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Decision;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\CredentialTrustResolutionFailurePolicyInterface;
use Sif\Foundation\Security\Exceptions\CredentialTrustResolutionUnavailableException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustCacheEntry;

final readonly class DefaultCredentialTrustResolutionFailurePolicy implements CredentialTrustResolutionFailurePolicyInterface
{
    public function __construct(
        private CredentialTrustResolutionFailureMode $mode = CredentialTrustResolutionFailureMode::FailClosed
    ) {
    }

    public function decide(
        ?CredentialTrustCacheEntry $cached,
        DateTimeImmutable $at,
        \Throwable $failure
    ): CredentialTrustDecision {
        if (
            $this->mode === CredentialTrustResolutionFailureMode::AllowUsableStale
            && $cached !== null
            && $cached->isUsableStaleAt($at)
        ) {
            return new CredentialTrustDecision(
                $cached->evidence(),
                true,
                true,
                true
            );
        }

        throw new CredentialTrustResolutionUnavailableException(
            'Credential trust resolution failed and no acceptable cached evidence is available.',
            0,
            $failure
        );
    }
}
