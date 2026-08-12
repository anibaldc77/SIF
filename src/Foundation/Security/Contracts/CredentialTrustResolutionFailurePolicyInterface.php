<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustCacheEntry;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\CredentialTrustDecision;

interface CredentialTrustResolutionFailurePolicyInterface
{
    public function decide(
        ?CredentialTrustCacheEntry $cached,
        DateTimeImmutable $at,
        \Throwable $failure
    ): CredentialTrustDecision;
}
