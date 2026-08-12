<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustCacheEntry;

interface CredentialTrustCacheInterface
{
    public function get(string $cacheKey): ?CredentialTrustCacheEntry;

    public function put(CredentialTrustCacheEntry $entry): void;

    public function forget(string $cacheKey): void;
}
