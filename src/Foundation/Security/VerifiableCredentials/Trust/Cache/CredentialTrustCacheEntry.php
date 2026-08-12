<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Cache;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialTrustCacheEntry
{
    public function __construct(
        private string $cacheKey,
        private CredentialTrustResolutionEvidence $evidence,
        private DateTimeImmutable $storedAt,
        private DateTimeImmutable $freshUntil,
        private DateTimeImmutable $staleUntil
    ) {
        if (
            trim($this->cacheKey) === ''
            || $this->freshUntil < $this->storedAt
            || $this->staleUntil < $this->freshUntil
        ) {
            throw new InvalidArgumentException(
                'Credential trust cache entry is invalid.'
            );
        }
    }

    public function cacheKey(): string
    {
        return $this->cacheKey;
    }

    public function evidence(): CredentialTrustResolutionEvidence
    {
        return $this->evidence;
    }

    public function storedAt(): DateTimeImmutable
    {
        return $this->storedAt;
    }

    public function freshUntil(): DateTimeImmutable
    {
        return $this->freshUntil;
    }

    public function staleUntil(): DateTimeImmutable
    {
        return $this->staleUntil;
    }

    public function isFreshAt(DateTimeImmutable $at): bool
    {
        return $at <= $this->freshUntil;
    }

    public function isUsableStaleAt(DateTimeImmutable $at): bool
    {
        return $at > $this->freshUntil && $at <= $this->staleUntil;
    }
}
