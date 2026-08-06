<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;
use Sif\Foundation\Security\Recovery\InMemoryRecoveryChallengeStore;
use Sif\Foundation\Security\Recovery\RecoveryChallenge;
use Sif\Foundation\Security\Recovery\RecoveryChallengeConsumptionStatus;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;
use Sif\Foundation\Security\Recovery\RecoveryChallengeRecord;
use Sif\Foundation\Security\Recovery\RecoveryChallengeState;
use Sif\Foundation\Security\Recovery\RecoverySubjectKey;
use Sif\Foundation\Security\Recovery\RecoveryToken;
use Sif\Foundation\Security\Recovery\RecoveryTokenDigest;

final class RecoveryChallengeLifecycleStoreTest extends TestCase
{
    public function testChallengeCanBeIssuedFoundAndConsumedExactlyOnce(): void
    {
        $store = new InMemoryRecoveryChallengeStore();
        $token = new RecoveryToken('abcdefghijklmnopqrstuvwxyzABCDE_0123456789-xyz');
        $record = $this->record('challenge-1', 'person@example.test', $token);

        $store->issue($record);

        self::assertSame($record, $store->find(new RecoveryChallengeId('challenge-1')));

        $result = $store->consume(
            new RecoveryChallengeId('challenge-1'),
            RecoveryChallengePurpose::PasswordReset,
            $token,
            new DateTimeImmutable('2026-08-06T15:05:00Z')
        );

        self::assertTrue($result->isConsumed());
        self::assertSame(RecoveryChallengeState::Consumed, $result->record()?->state());

        $replay = $store->consume(
            new RecoveryChallengeId('challenge-1'),
            RecoveryChallengePurpose::PasswordReset,
            $token,
            new DateTimeImmutable('2026-08-06T15:06:00Z')
        );

        self::assertSame(RecoveryChallengeConsumptionStatus::AlreadyConsumed, $replay->status());
    }

    public function testPurposeTokenExpirationAndRevocationFailClosed(): void
    {
        $store = new InMemoryRecoveryChallengeStore();
        $token = new RecoveryToken('abcdefghijklmnopqrstuvwxyzABCDE_0123456789-xyz');
        $store->issue($this->record('challenge-2', 'person@example.test', $token));

        $wrongPurpose = $store->consume(
            new RecoveryChallengeId('challenge-2'),
            RecoveryChallengePurpose::IdentityVerification,
            $token,
            new DateTimeImmutable('2026-08-06T15:05:00Z')
        );
        self::assertSame(RecoveryChallengeConsumptionStatus::PurposeMismatch, $wrongPurpose->status());

        $wrongToken = $store->consume(
            new RecoveryChallengeId('challenge-2'),
            RecoveryChallengePurpose::PasswordReset,
            new RecoveryToken('ABCDEFGHIJKLMNOPQRSTUVWXYZabcde_0123456789-xyz'),
            new DateTimeImmutable('2026-08-06T15:05:00Z')
        );
        self::assertSame(RecoveryChallengeConsumptionStatus::TokenMismatch, $wrongToken->status());

        self::assertTrue($store->revoke(
            new RecoveryChallengeId('challenge-2'),
            new DateTimeImmutable('2026-08-06T15:06:00Z')
        ));

        $revoked = $store->consume(
            new RecoveryChallengeId('challenge-2'),
            RecoveryChallengePurpose::PasswordReset,
            $token,
            new DateTimeImmutable('2026-08-06T15:07:00Z')
        );
        self::assertSame(RecoveryChallengeConsumptionStatus::Revoked, $revoked->status());

        $expiredStore = new InMemoryRecoveryChallengeStore();
        $expiredStore->issue($this->record('challenge-3', 'person@example.test', $token));
        $expired = $expiredStore->consume(
            new RecoveryChallengeId('challenge-3'),
            RecoveryChallengePurpose::PasswordReset,
            $token,
            new DateTimeImmutable('2026-08-06T15:15:00Z')
        );
        self::assertSame(RecoveryChallengeConsumptionStatus::Expired, $expired->status());
    }

    public function testOutstandingChallengesCanBeRevokedBySubjectAndPurpose(): void
    {
        $store = new InMemoryRecoveryChallengeStore();
        $token = new RecoveryToken('abcdefghijklmnopqrstuvwxyzABCDE_0123456789-xyz');
        $store->issue($this->record('challenge-4', 'person@example.test', $token));
        $store->issue($this->record('challenge-5', 'person@example.test', $token));
        $store->issue($this->record(
            'challenge-6',
            'person@example.test',
            $token,
            RecoveryChallengePurpose::IdentityVerification
        ));

        $count = $store->revokeOutstanding(
            new RecoverySubjectKey('person@example.test'),
            RecoveryChallengePurpose::PasswordReset,
            new DateTimeImmutable('2026-08-06T15:05:00Z')
        );

        self::assertSame(2, $count);
        self::assertSame(
            RecoveryChallengeState::Revoked,
            $store->find(new RecoveryChallengeId('challenge-4'))?->state()
        );
        self::assertSame(
            RecoveryChallengeState::Pending,
            $store->find(new RecoveryChallengeId('challenge-6'))?->state()
        );
    }

    public function testExpiredRecordsCanBePurgedAndIdentifiersRemainUnique(): void
    {
        $store = new InMemoryRecoveryChallengeStore();
        $token = new RecoveryToken('abcdefghijklmnopqrstuvwxyzABCDE_0123456789-xyz');
        $record = $this->record('challenge-7', 'person@example.test', $token);
        $store->issue($record);

        self::assertSame(1, $store->purgeExpired(new DateTimeImmutable('2026-08-06T15:15:00Z')));
        self::assertNull($store->find(new RecoveryChallengeId('challenge-7')));

        $store->issue($record);

        $this->expectException(InvalidRecoveryChallengeException::class);
        $store->issue($record);
    }

    public function testSnapshotsNeverExposeDigestTokenOrSubject(): void
    {
        $token = new RecoveryToken('abcdefghijklmnopqrstuvwxyzABCDE_0123456789-xyz');
        $snapshot = $this->record('challenge-8', 'person@example.test', $token)->snapshot();
        $serialized = json_encode($snapshot, JSON_THROW_ON_ERROR);

        self::assertArrayNotHasKey('token', $snapshot);
        self::assertArrayNotHasKey('token_digest', $snapshot);
        self::assertArrayNotHasKey('subject', $snapshot);
        self::assertStringNotContainsString('person@example.test', $serialized);
        self::assertStringNotContainsString('abcdefghijklmnopqrstuvwxyz', $serialized);
    }

    private function record(
        string $id,
        string $subject,
        RecoveryToken $token,
        RecoveryChallengePurpose $purpose = RecoveryChallengePurpose::PasswordReset
    ): RecoveryChallengeRecord {
        return new RecoveryChallengeRecord(
            new RecoveryChallenge(
                new RecoveryChallengeId($id),
                $purpose,
                new RecoverySubjectKey($subject),
                new DateTimeImmutable('2026-08-06T15:00:00Z'),
                new DateTimeImmutable('2026-08-06T15:15:00Z')
            ),
            RecoveryTokenDigest::fromToken($token)
        );
    }
}
