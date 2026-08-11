<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusCacheEntry;

interface CredentialStatusRefreshPolicyInterface
{
    public function shouldRefresh(
        CredentialStatusCacheEntry $entry,
        DateTimeImmutable $at
    ): bool;
}
