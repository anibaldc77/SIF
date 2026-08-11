<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusCacheEntry;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusResolutionDecision;

interface CredentialStatusResolutionFailurePolicyInterface
{
    public function decide(
        ?CredentialStatusCacheEntry $cached,
        DateTimeImmutable $at,
        \Throwable $failure
    ): CredentialStatusResolutionDecision;
}
