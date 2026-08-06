<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;

final readonly class RecoveryChallenge
{
    private DateTimeImmutable $issuedAt;

    private DateTimeImmutable $expiresAt;

    public function __construct(
        private RecoveryChallengeId $id,
        private RecoveryChallengePurpose $purpose,
        private RecoverySubjectKey $subject,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $expiresAt
    ) {
        $utc = new DateTimeZone('UTC');
        $normalizedIssuedAt = $issuedAt->setTimezone($utc);
        $normalizedExpiresAt = $expiresAt->setTimezone($utc);

        if ($normalizedExpiresAt <= $normalizedIssuedAt) {
            throw new InvalidRecoveryChallengeException(
                'Recovery challenge expiration must be later than its issue time.'
            );
        }

        $this->issuedAt = $normalizedIssuedAt;
        $this->expiresAt = $normalizedExpiresAt;
    }

    public function id(): RecoveryChallengeId
    {
        return $this->id;
    }

    public function purpose(): RecoveryChallengePurpose
    {
        return $this->purpose;
    }

    public function subject(): RecoverySubjectKey
    {
        return $this->subject;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpiredAt(DateTimeImmutable $instant): bool
    {
        return $instant->setTimezone(new DateTimeZone('UTC')) >= $this->expiresAt;
    }

    /**
     * @return array{id: string, purpose: string, subject_fingerprint: string, issued_at: string, expires_at: string}
     */
    public function snapshot(): array
    {
        return [
            'id' => $this->id->value(),
            'purpose' => $this->purpose->value,
            'subject_fingerprint' => $this->subject->fingerprint(),
            'issued_at' => $this->issuedAt->format(DATE_ATOM),
            'expires_at' => $this->expiresAt->format(DATE_ATOM),
        ];
    }
}
