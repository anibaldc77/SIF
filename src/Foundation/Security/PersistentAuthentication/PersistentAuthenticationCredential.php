<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Exceptions\InvalidPersistentAuthenticationCredentialException;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class PersistentAuthenticationCredential
{
    private DateTimeImmutable $issuedAt;

    private DateTimeImmutable $absoluteExpiresAt;

    private ?DateTimeImmutable $revokedAt;

    public function __construct(
        private PersistentAuthenticationSelector $selector,
        private IdentityId $identityId,
        private PersistentAuthenticationValidatorDigest $validatorDigest,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $absoluteExpiresAt,
        private PersistentAuthenticationCredentialStatus $status =
            PersistentAuthenticationCredentialStatus::Active,
        ?DateTimeImmutable $revokedAt = null
    ) {
        $utc = new DateTimeZone('UTC');
        $normalizedIssuedAt = $issuedAt->setTimezone($utc);
        $normalizedExpiresAt = $absoluteExpiresAt->setTimezone($utc);
        $normalizedRevokedAt = $revokedAt?->setTimezone($utc);

        if ($normalizedExpiresAt <= $normalizedIssuedAt) {
            throw new InvalidPersistentAuthenticationCredentialException(
                'Persistent authentication expiration must be later than issuance.'
            );
        }

        if (
            $status === PersistentAuthenticationCredentialStatus::Revoked
            && $normalizedRevokedAt === null
        ) {
            throw new InvalidPersistentAuthenticationCredentialException(
                'Revoked persistent authentication credential requires revokedAt.'
            );
        }

        $this->issuedAt = $normalizedIssuedAt;
        $this->absoluteExpiresAt = $normalizedExpiresAt;
        $this->revokedAt = $normalizedRevokedAt;
    }

    public function selector(): PersistentAuthenticationSelector
    {
        return $this->selector;
    }

    public function identityId(): IdentityId
    {
        return $this->identityId;
    }

    public function validatorDigest(): PersistentAuthenticationValidatorDigest
    {
        return $this->validatorDigest;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function absoluteExpiresAt(): DateTimeImmutable
    {
        return $this->absoluteExpiresAt;
    }

    public function status(): PersistentAuthenticationCredentialStatus
    {
        return $this->status;
    }

    public function isExpiredAt(DateTimeImmutable $now): bool
    {
        return $now->setTimezone(new DateTimeZone('UTC')) >= $this->absoluteExpiresAt;
    }

    public function revoke(DateTimeImmutable $at): self
    {
        if ($this->status === PersistentAuthenticationCredentialStatus::Revoked) {
            return $this;
        }

        return new self(
            $this->selector,
            $this->identityId,
            $this->validatorDigest,
            $this->issuedAt,
            $this->absoluteExpiresAt,
            PersistentAuthenticationCredentialStatus::Revoked,
            $at
        );
    }

    /**
     * @return array{
     *     selector_fingerprint:string,
     *     identity_fingerprint:string,
     *     status:string,
     *     issued_at:string,
     *     absolute_expires_at:string,
     *     revoked:bool
     * }
     */
    public function snapshot(): array
    {
        return [
            'selector_fingerprint' => hash('sha256', $this->selector->value()),
            'identity_fingerprint' => hash('sha256', $this->identityId->value()),
            'status' => $this->status->value,
            'issued_at' => $this->issuedAt->format(DATE_ATOM),
            'absolute_expires_at' => $this->absoluteExpiresAt->format(DATE_ATOM),
            'revoked' => $this->revokedAt !== null,
        ];
    }
}
