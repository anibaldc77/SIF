<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Recovery\RecoveryChallengeConsumptionResult;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;
use Sif\Foundation\Security\Recovery\RecoveryChallengeRecord;
use Sif\Foundation\Security\Recovery\RecoverySubjectKey;
use Sif\Foundation\Security\Recovery\RecoveryToken;

interface RecoveryChallengeStoreInterface
{
    public function issue(RecoveryChallengeRecord $record): void;

    public function find(RecoveryChallengeId $id): ?RecoveryChallengeRecord;

    public function consume(
        RecoveryChallengeId $id,
        RecoveryChallengePurpose $purpose,
        RecoveryToken $token,
        DateTimeImmutable $instant
    ): RecoveryChallengeConsumptionResult;

    public function revoke(RecoveryChallengeId $id, DateTimeImmutable $instant): bool;

    public function revokeOutstanding(
        RecoverySubjectKey $subject,
        RecoveryChallengePurpose $purpose,
        DateTimeImmutable $instant
    ): int;

    public function purgeExpired(DateTimeImmutable $instant): int;
}
