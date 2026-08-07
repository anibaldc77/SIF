<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\RecoveryCode;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class RecoveryCodeRecord
{
    private DateTimeImmutable $issuedAt;
    private ?DateTimeImmutable $consumedAt;

    public function __construct(
        private IdentityId $identityId,
        private RecoveryCodeDigest $digest,
        DateTimeImmutable $issuedAt,
        ?DateTimeImmutable $consumedAt = null
    ) {
        $utc = new DateTimeZone('UTC');
        $this->issuedAt = $issuedAt->setTimezone($utc);
        $this->consumedAt = $consumedAt?->setTimezone($utc);
    }

    public function identityId(): IdentityId
    {
        return $this->identityId;
    }

    public function digest(): RecoveryCodeDigest
    {
        return $this->digest;
    }

    public function isConsumed(): bool
    {
        return $this->consumedAt !== null;
    }

    public function consume(DateTimeImmutable $at): self
    {
        if ($this->isConsumed()) {
            return $this;
        }

        return new self($this->identityId, $this->digest, $this->issuedAt, $at);
    }

    /** @return array{identity_fingerprint:string,digest_fingerprint:string,issued_at:string,consumed:bool} */
    public function snapshot(): array
    {
        return [
            'identity_fingerprint' => hash('sha256', $this->identityId->value()),
            'digest_fingerprint' => hash('sha256', $this->digest->value()),
            'issued_at' => $this->issuedAt->format(DATE_ATOM),
            'consumed' => $this->isConsumed(),
        ];
    }
}
