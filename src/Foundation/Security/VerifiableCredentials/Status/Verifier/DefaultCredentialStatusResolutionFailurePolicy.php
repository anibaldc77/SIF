<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Verifier;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\CredentialStatusResolutionFailurePolicyInterface;
use Sif\Foundation\Security\Exceptions\CredentialStatusResolutionUnavailableException;

final readonly class DefaultCredentialStatusResolutionFailurePolicy implements CredentialStatusResolutionFailurePolicyInterface
{
    public function __construct(
        private CredentialStatusResolutionFailureMode $mode = CredentialStatusResolutionFailureMode::FailClosed
    ) {
    }

    public function decide(
        ?CredentialStatusCacheEntry $cached,
        DateTimeImmutable $at,
        \Throwable $failure
    ): CredentialStatusResolutionDecision {
        if (
            $this->mode === CredentialStatusResolutionFailureMode::AllowUsableStale
            && $cached !== null
            && $cached->isUsableStaleAt($at)
        ) {
            return new CredentialStatusResolutionDecision(
                $cached->result(),
                true,
                true,
                true
            );
        }

        throw new CredentialStatusResolutionUnavailableException(
            'Credential status resolution failed and no acceptable cached evidence is available.',
            0,
            $failure
        );
    }
}