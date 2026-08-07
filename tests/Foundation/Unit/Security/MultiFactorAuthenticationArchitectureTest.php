<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Contracts\MultiFactorChallengeStoreInterface;
use Sif\Foundation\Security\Exceptions\InvalidMultiFactorChallengeException;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallenge;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeId;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengePurpose;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeStatus;
use Sif\Foundation\Security\MultiFactor\MultiFactorType;

final class MultiFactorAuthenticationArchitectureTest extends TestCase
{
    public function testFactorTypesRemainExtensibleAndPurposesRemainExplicit(): void
    {
        self::assertSame('totp', MultiFactorType::totp()->value());
        self::assertSame('recovery_code', MultiFactorType::recoveryCode()->value());
        self::assertSame('hardware_token', (new MultiFactorType('hardware_token'))->value());
        self::assertNotSame(
            MultiFactorChallengePurpose::AuthenticationContinuation,
            MultiFactorChallengePurpose::StepUp
        );
    }

    public function testChallengeSnapshotIsDeterministicAndNonSensitive(): void
    {
        $challenge = new MultiFactorChallenge(
            new MultiFactorChallengeId('MFA:ABC-1001'),
            new IdentityId('user-1001'),
            MultiFactorType::totp(),
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(60),
            new DateTimeImmutable('2026-08-06T12:00:00-03:00'),
            new DateTimeImmutable('2026-08-06T12:05:00-03:00')
        );

        $snapshot = $challenge->snapshot();

        self::assertSame('mfa:abc-1001', $snapshot['id']);
        self::assertSame(64, strlen($snapshot['identity_fingerprint']));
        self::assertSame('totp', $snapshot['factor_type']);
        self::assertSame('step_up', $snapshot['purpose']);
        self::assertSame(60, $snapshot['required_level']);
        self::assertSame('pending', $snapshot['status']);
        self::assertSame('2026-08-06T15:00:00+00:00', $snapshot['issued_at']);
        self::assertSame('2026-08-06T15:05:00+00:00', $snapshot['expires_at']);
        self::assertArrayNotHasKey('identity_id', $snapshot);
        self::assertArrayNotHasKey('secret', $snapshot);
        self::assertArrayNotHasKey('code', $snapshot);
    }

    public function testExpirationBoundaryAndAuthenticationLevelAreExplicit(): void
    {
        $challenge = new MultiFactorChallenge(
            new MultiFactorChallengeId('mfa-2001'),
            new IdentityId('user-2001'),
            MultiFactorType::recoveryCode(),
            MultiFactorChallengePurpose::AuthenticationContinuation,
            new AuthenticationLevel(50),
            new DateTimeImmutable('2026-08-06T15:00:00Z'),
            new DateTimeImmutable('2026-08-06T15:10:00Z'),
            MultiFactorChallengeStatus::Pending
        );

        self::assertFalse($challenge->isExpiredAt(new DateTimeImmutable('2026-08-06T15:09:59Z')));
        self::assertTrue($challenge->isExpiredAt(new DateTimeImmutable('2026-08-06T15:10:00Z')));
        self::assertSame(50, $challenge->requiredLevel()->value());
    }

    public function testStoreContractRemainsPersistenceAndProviderNeutral(): void
    {
        $reflection = new \ReflectionClass(MultiFactorChallengeStoreInterface::class);
        $source = file_get_contents((string) $reflection->getFileName());

        self::assertIsString($source);
        self::assertStringNotContainsString('BaseModel', $source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('Totp', $source);
        self::assertStringNotContainsString('Keycloak', $source);

        $this->expectException(InvalidMultiFactorChallengeException::class);
        new MultiFactorChallenge(
            new MultiFactorChallengeId('invalid-window'),
            new IdentityId('user-3001'),
            MultiFactorType::totp(),
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(60),
            new DateTimeImmutable('2026-08-06T15:00:00Z'),
            new DateTimeImmutable('2026-08-06T15:00:00Z')
        );
    }
}
