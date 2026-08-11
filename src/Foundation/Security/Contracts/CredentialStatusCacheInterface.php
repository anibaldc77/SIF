<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusCacheEntry;

interface CredentialStatusCacheInterface
{
    public function get(string $cacheKey): ?CredentialStatusCacheEntry;

    public function put(CredentialStatusCacheEntry $entry): void;

    public function forget(string $cacheKey): void;
}
