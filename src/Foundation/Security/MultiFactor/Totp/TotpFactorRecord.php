<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Exceptions\InvalidTotpFactorException;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class TotpFactorRecord
{
    private DateTimeImmutable $enrolledAt;

    private ?DateTimeImmutable $activatedAt;

    public function __construct(
        private TotpFactorId $id,
        private IdentityId $identityId,
        private TotpSecret $secret,
        private TotpParameters $parameters,
        private TotpFactorStatus $status,
        DateTimeImmutable $enrolledAt,
        ?DateTimeImmutable $activatedAt = null,
        private ?int $lastAcceptedCounter = null
    ) {
        $utc = new DateTimeZone('UTC');
        $normalizedEnrolledAt = $enrolledAt->setTimezone($utc);
        $normalizedActivatedAt = $activatedAt?->setTimezone($utc);

        if ($lastAcceptedCounter !== null && $lastAcceptedCounter < 0) {
            throw new InvalidTotpFactorException('TOTP accepted counter cannot be negative.');
        }

        if ($status === TotpFactorStatus::Pending && ($normalizedActivatedAt !== null || $lastAcceptedCounter !== null)) {
            throw new InvalidTotpFactorException('Pending TOTP factor cannot have activation state.');
        }

        if ($status === TotpFactorStatus::Active && ($normalizedActivatedAt === null || $lastAcceptedCounter === null)) {
            throw new InvalidTotpFactorException('Active TOTP factor requires activation time and accepted counter.');
        }

        if ($normalizedActivatedAt !== null && $normalizedActivatedAt < $normalizedEnrolledAt) {
            throw new InvalidTotpFactorException('TOTP activation cannot precede enrollment.');
        }

        $this->enrolledAt = $normalizedEnrolledAt;
        $this->activatedAt = $normalizedActivatedAt;
    }

    public static function pending(
        TotpFactorId $id,
        IdentityId $identityId,
        TotpSecret $secret,
        TotpParameters $parameters,
        DateTimeImmutable $enrolledAt
    ): self {
        return new self(
            $id,
            $identityId,
            $secret,
            $parameters,
            TotpFactorStatus::Pending,
            $enrolledAt
        );
    }

    public function id(): TotpFactorId
    {
        return $this->id;
    }

    public function identityId(): IdentityId
    {
        return $this->identityId;
    }

    public function secret(): TotpSecret
    {
        return $this->secret;
    }

    public function parameters(): TotpParameters
    {
        return $this->parameters;
    }

    public function status(): TotpFactorStatus
    {
        return $this->status;
    }

    public function lastAcceptedCounter(): ?int
    {
        return $this->lastAcceptedCounter;
    }

    public function activate(DateTimeImmutable $activatedAt, int $acceptedCounter): self
    {
        if ($this->status !== TotpFactorStatus::Pending) {
            throw new InvalidTotpFactorException('Only pending TOTP factors can be activated.');
        }

        return new self(
            $this->id,
            $this->identityId,
            $this->secret,
            $this->parameters,
            TotpFactorStatus::Active,
            $this->enrolledAt,
            $activatedAt,
            $acceptedCounter
        );
    }

    public function withAcceptedCounter(int $acceptedCounter): self
    {
        if ($this->status !== TotpFactorStatus::Active) {
            throw new InvalidTotpFactorException('Only active TOTP factors can accept counters.');
        }

        if ($this->lastAcceptedCounter !== null && $acceptedCounter <= $this->lastAcceptedCounter) {
            throw new InvalidTotpFactorException('TOTP counter must advance monotonically.');
        }

        return new self(
            $this->id,
            $this->identityId,
            $this->secret,
            $this->parameters,
            $this->status,
            $this->enrolledAt,
            $this->activatedAt,
            $acceptedCounter
        );
    }

    public function revoke(): self
    {
        return new self(
            $this->id,
            $this->identityId,
            $this->secret,
            $this->parameters,
            TotpFactorStatus::Revoked,
            $this->enrolledAt,
            $this->activatedAt,
            $this->lastAcceptedCounter
        );
    }

    /** @return array{id: string, identity_fingerprint: string, status: string, parameters: array{algorithm: string, digits: int, period_seconds: int, allowed_past_windows: int, allowed_future_windows: int}, enrolled_at: string, activated_at: ?string, last_accepted_counter: ?int} */
    public function snapshot(): array
    {
        return [
            'id' => $this->id->value(),
            'identity_fingerprint' => hash('sha256', $this->identityId->value()),
            'status' => $this->status->value,
            'parameters' => $this->parameters->snapshot(),
            'enrolled_at' => $this->enrolledAt->format(DATE_ATOM),
            'activated_at' => $this->activatedAt?->format(DATE_ATOM),
            'last_accepted_counter' => $this->lastAcceptedCounter,
        ];
    }
}
