<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;

final readonly class RecoveryChallengeRecord
{
    private ?DateTimeImmutable $consumedAt;

    private ?DateTimeImmutable $revokedAt;

    public function __construct(
        private RecoveryChallenge $challenge,
        private RecoveryTokenDigest $tokenDigest,
        private RecoveryChallengeState $state = RecoveryChallengeState::Pending,
        ?DateTimeImmutable $consumedAt = null,
        ?DateTimeImmutable $revokedAt = null
    ) {
        $utc = new DateTimeZone('UTC');
        $normalizedConsumedAt = $consumedAt?->setTimezone($utc);
        $normalizedRevokedAt = $revokedAt?->setTimezone($utc);

        if ($state === RecoveryChallengeState::Pending && ($consumedAt !== null || $revokedAt !== null)) {
            throw new InvalidRecoveryChallengeException('Pending recovery challenge cannot have terminal timestamps.');
        }

        if ($state === RecoveryChallengeState::Consumed && ($consumedAt === null || $revokedAt !== null)) {
            throw new InvalidRecoveryChallengeException('Consumed recovery challenge requires only a consumption time.');
        }

        if ($state === RecoveryChallengeState::Revoked && ($revokedAt === null || $consumedAt !== null)) {
            throw new InvalidRecoveryChallengeException('Revoked recovery challenge requires only a revocation time.');
        }

        $this->consumedAt = $normalizedConsumedAt;
        $this->revokedAt = $normalizedRevokedAt;
    }

    public function challenge(): RecoveryChallenge
    {
        return $this->challenge;
    }

    public function tokenDigest(): RecoveryTokenDigest
    {
        return $this->tokenDigest;
    }

    public function state(): RecoveryChallengeState
    {
        return $this->state;
    }

    public function consumedAt(): ?DateTimeImmutable
    {
        return $this->consumedAt;
    }

    public function revokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function isUsableAt(DateTimeImmutable $instant): bool
    {
        return $this->state === RecoveryChallengeState::Pending
            && !$this->challenge->isExpiredAt($instant);
    }

    public function consumeAt(DateTimeImmutable $instant): self
    {
        if (!$this->isUsableAt($instant)) {
            throw new InvalidRecoveryChallengeException('Only a pending, unexpired recovery challenge can be consumed.');
        }

        return new self(
            $this->challenge,
            $this->tokenDigest,
            RecoveryChallengeState::Consumed,
            $instant
        );
    }

    public function revokeAt(DateTimeImmutable $instant): self
    {
        if ($this->state !== RecoveryChallengeState::Pending) {
            throw new InvalidRecoveryChallengeException('Only a pending recovery challenge can be revoked.');
        }

        return new self(
            $this->challenge,
            $this->tokenDigest,
            RecoveryChallengeState::Revoked,
            null,
            $instant
        );
    }

    /**
     * @return array{id: string, purpose: string, subject_fingerprint: string, state: string, issued_at: string, expires_at: string, consumed_at: ?string, revoked_at: ?string}
     */
    public function snapshot(): array
    {
        $challenge = $this->challenge->snapshot();

        return [
            ...$challenge,
            'state' => $this->state->value,
            'consumed_at' => $this->consumedAt?->format(DATE_ATOM),
            'revoked_at' => $this->revokedAt?->format(DATE_ATOM),
        ];
    }
}
