<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\Events;

use DateTimeImmutable;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;

final readonly class RecoverySecurityEvent
{
    public function __construct(
        private RecoverySecurityEventType $type,
        private RecoveryChallengePurpose $purpose,
        private string $subjectFingerprint,
        private DateTimeImmutable $occurredAt,
        private ?RecoveryChallengeId $challengeId = null
    ) {
    }

    public function type(): RecoverySecurityEventType
    {
        return $this->type;
    }

    /** @return array{type: string, purpose: string, subject_fingerprint: string, occurred_at: string, challenge_id: ?string} */
    public function snapshot(): array
    {
        return [
            'type' => $this->type->value,
            'purpose' => $this->purpose->value,
            'subject_fingerprint' => $this->subjectFingerprint,
            'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            'challenge_id' => $this->challengeId?->value(),
        ];
    }
}
