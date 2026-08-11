<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Verifier;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusEvidence;

final readonly class CredentialStatusCacheEntry
{
    public function __construct(
        private string $cacheKey,
        private CredentialStatusEvidence $result,
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
                'Credential status cache entry is invalid.'
            );
        }
    }

    public function cacheKey(): string { return $this->cacheKey; }
    public function result(): CredentialStatusEvidence { return $this->result; }
    public function storedAt(): DateTimeImmutable { return $this->storedAt; }
    public function freshUntil(): DateTimeImmutable { return $this->freshUntil; }
    public function staleUntil(): DateTimeImmutable { return $this->staleUntil; }

    public function isFreshAt(DateTimeImmutable $at): bool
    {
        return $at <= $this->freshUntil;
    }

    public function isUsableStaleAt(DateTimeImmutable $at): bool
    {
        return $at > $this->freshUntil && $at <= $this->staleUntil;
    }
}

