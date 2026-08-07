<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Exceptions\InvalidTrustedDeviceGrantException;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class TrustedDeviceGrant
{
    private DateTimeImmutable $issuedAt;

    private DateTimeImmutable $expiresAt;

    private ?DateTimeImmutable $revokedAt;

    public function __construct(
        private TrustedDeviceGrantId $id,
        private IdentityId $identityId,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $expiresAt,
        private TrustedDeviceGrantStatus $status = TrustedDeviceGrantStatus::Active,
        ?DateTimeImmutable $revokedAt = null
    ) {
        $utc = new DateTimeZone('UTC');
        $normalizedIssuedAt = $issuedAt->setTimezone($utc);
        $normalizedExpiresAt = $expiresAt->setTimezone($utc);
        $normalizedRevokedAt = $revokedAt?->setTimezone($utc);

        if ($normalizedExpiresAt <= $normalizedIssuedAt) {
            throw new InvalidTrustedDeviceGrantException(
                'Trusted-device grant expiration must be later than issuance.'
            );
        }

        if (
            $status === TrustedDeviceGrantStatus::Revoked
            && $normalizedRevokedAt === null
        ) {
            throw new InvalidTrustedDeviceGrantException(
                'Revoked trusted-device grant requires revokedAt.'
            );
        }

        $this->issuedAt = $normalizedIssuedAt;
        $this->expiresAt = $normalizedExpiresAt;
        $this->revokedAt = $normalizedRevokedAt;
    }

    public function id(): TrustedDeviceGrantId
    {
        return $this->id;
    }

    public function identityId(): IdentityId
    {
        return $this->identityId;
    }

    public function status(): TrustedDeviceGrantStatus
    {
        return $this->status;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpiredAt(DateTimeImmutable $now): bool
    {
        return $now->setTimezone(new DateTimeZone('UTC')) >= $this->expiresAt;
    }

    public function isUsableAt(DateTimeImmutable $now): bool
    {
        return $this->status === TrustedDeviceGrantStatus::Active
            && !$this->isExpiredAt($now);
    }

    public function revoke(DateTimeImmutable $at): self
    {
        if ($this->status === TrustedDeviceGrantStatus::Revoked) {
            return $this;
        }

        return new self(
            $this->id,
            $this->identityId,
            $this->issuedAt,
            $this->expiresAt,
            TrustedDeviceGrantStatus::Revoked,
            $at
        );
    }

    /**
     * @return array{
     *     id_fingerprint:string,
     *     identity_fingerprint:string,
     *     status:string,
     *     issued_at:string,
     *     expires_at:string,
     *     revoked:bool
     * }
     */
    public function snapshot(): array
    {
        return [
            'id_fingerprint' => hash('sha256', $this->id->value()),
            'identity_fingerprint' => hash('sha256', $this->identityId->value()),
            'status' => $this->status->value,
            'issued_at' => $this->issuedAt->format(DATE_ATOM),
            'expires_at' => $this->expiresAt->format(DATE_ATOM),
            'revoked' => $this->revokedAt !== null,
        ];
    }
}
