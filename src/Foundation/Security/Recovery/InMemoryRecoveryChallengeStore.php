<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\RecoveryChallengeStoreInterface;
use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;

final class InMemoryRecoveryChallengeStore implements RecoveryChallengeStoreInterface
{
    /** @var array<string, RecoveryChallengeRecord> */
    private array $records = [];

    public function issue(RecoveryChallengeRecord $record): void
    {
        $id = $record->challenge()->id()->value();

        if (isset($this->records[$id])) {
            throw new InvalidRecoveryChallengeException('Recovery challenge identifier is already registered.');
        }

        $this->records[$id] = $record;
    }

    public function find(RecoveryChallengeId $id): ?RecoveryChallengeRecord
    {
        return $this->records[$id->value()] ?? null;
    }

    public function consume(
        RecoveryChallengeId $id,
        RecoveryChallengePurpose $purpose,
        RecoveryToken $token,
        DateTimeImmutable $instant
    ): RecoveryChallengeConsumptionResult {
        $record = $this->find($id);

        if ($record === null) {
            return RecoveryChallengeConsumptionResult::rejected(
                RecoveryChallengeConsumptionStatus::NotFound
            );
        }

        if ($record->challenge()->purpose() !== $purpose) {
            return RecoveryChallengeConsumptionResult::rejected(
                RecoveryChallengeConsumptionStatus::PurposeMismatch
            );
        }

        if ($record->state() === RecoveryChallengeState::Consumed) {
            return RecoveryChallengeConsumptionResult::rejected(
                RecoveryChallengeConsumptionStatus::AlreadyConsumed
            );
        }

        if ($record->state() === RecoveryChallengeState::Revoked) {
            return RecoveryChallengeConsumptionResult::rejected(
                RecoveryChallengeConsumptionStatus::Revoked
            );
        }

        if ($record->challenge()->isExpiredAt($instant)) {
            return RecoveryChallengeConsumptionResult::rejected(
                RecoveryChallengeConsumptionStatus::Expired
            );
        }

        if (!$record->tokenDigest()->matches($token)) {
            return RecoveryChallengeConsumptionResult::rejected(
                RecoveryChallengeConsumptionStatus::TokenMismatch
            );
        }

        $consumed = $record->consumeAt($instant);
        $this->records[$id->value()] = $consumed;

        return RecoveryChallengeConsumptionResult::consumed($consumed);
    }

    public function revoke(RecoveryChallengeId $id, DateTimeImmutable $instant): bool
    {
        $record = $this->find($id);

        if ($record === null || $record->state() !== RecoveryChallengeState::Pending) {
            return false;
        }

        $this->records[$id->value()] = $record->revokeAt($instant);

        return true;
    }

    public function revokeOutstanding(
        RecoverySubjectKey $subject,
        RecoveryChallengePurpose $purpose,
        DateTimeImmutable $instant
    ): int {
        $revoked = 0;

        foreach ($this->records as $id => $record) {
            if (
                $record->state() !== RecoveryChallengeState::Pending
                || $record->challenge()->purpose() !== $purpose
                || !hash_equals(
                    $record->challenge()->subject()->value(),
                    $subject->value()
                )
            ) {
                continue;
            }

            $this->records[$id] = $record->revokeAt($instant);
            $revoked++;
        }

        return $revoked;
    }

    public function purgeExpired(DateTimeImmutable $instant): int
    {
        $purged = 0;

        foreach ($this->records as $id => $record) {
            if (!$record->challenge()->isExpiredAt($instant)) {
                continue;
            }

            unset($this->records[$id]);
            $purged++;
        }

        return $purged;
    }
}
