<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustCacheEntry;

interface CredentialTrustRefreshPolicyInterface
{
    public function shouldRefresh(
        CredentialTrustCacheEntry $entry,
        DateTimeImmutable $at
    ): bool;
}
