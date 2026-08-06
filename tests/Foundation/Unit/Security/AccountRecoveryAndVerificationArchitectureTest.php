<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\RecoveryChallengeStoreInterface;
use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;
use Sif\Foundation\Security\Recovery\RecoveryChallenge;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;
use Sif\Foundation\Security\Recovery\RecoverySubjectKey;

final class AccountRecoveryAndVerificationArchitectureTest extends TestCase
{
    public function testChallengePurposesRemainExplicitAndNonInterchangeable(): void
    {
        self::assertSame('password_reset', RecoveryChallengePurpose::PasswordReset->value);
        self::assertSame('identity_verification', RecoveryChallengePurpose::IdentityVerification->value);
        self::assertNotSame(
            RecoveryChallengePurpose::PasswordReset,
            RecoveryChallengePurpose::IdentityVerification
        );
    }

    public function testChallengeIdentityAndSubjectAreStableAndSafe(): void
    {
        $first = new RecoveryChallengeId(' Reset:ABC-123 ');
        $second = new RecoveryChallengeId('reset:abc-123');
        $subject = new RecoverySubjectKey('User.Name@example.test');

        self::assertTrue($first->equals($second));
        self::assertSame('reset:abc-123', $first->value());
        self::assertSame(64, strlen($subject->fingerprint()));
        self::assertNotSame($subject->value(), $subject->fingerprint());
    }

    public function testChallengeExpirationAndSnapshotAreDeterministicAndNonSensitive(): void
    {
        $challenge = new RecoveryChallenge(
            new RecoveryChallengeId('challenge-1001'),
            RecoveryChallengePurpose::PasswordReset,
            new RecoverySubjectKey('person@example.test'),
            new DateTimeImmutable('2026-08-06T12:00:00-03:00'),
            new DateTimeImmutable('2026-08-06T12:15:00-03:00')
        );

        $snapshot = $challenge->snapshot();

        self::assertSame('2026-08-06T15:00:00+00:00', $snapshot['issued_at']);
        self::assertSame('2026-08-06T15:15:00+00:00', $snapshot['expires_at']);
        self::assertArrayNotHasKey('token', $snapshot);
        self::assertArrayNotHasKey('subject', $snapshot);
        self::assertFalse($challenge->isExpiredAt(new DateTimeImmutable('2026-08-06T15:14:59Z')));
        self::assertTrue($challenge->isExpiredAt(new DateTimeImmutable('2026-08-06T15:15:00Z')));
    }

    public function testStoreContractRemainsPersistenceAndDeliveryNeutral(): void
    {
        $reflection = new \ReflectionClass(RecoveryChallengeStoreInterface::class);
        $source = file_get_contents((string) $reflection->getFileName());

        self::assertIsString($source);
        self::assertStringNotContainsString('BaseModel', $source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('Mailer', $source);
        self::assertStringNotContainsString('Sms', $source);

        $this->expectException(InvalidRecoveryChallengeException::class);
        new RecoveryChallenge(
            new RecoveryChallengeId('invalid-window'),
            RecoveryChallengePurpose::IdentityVerification,
            new RecoverySubjectKey('subject-1'),
            new DateTimeImmutable('2026-08-06T15:00:00Z'),
            new DateTimeImmutable('2026-08-06T15:00:00Z')
        );
    }
}
