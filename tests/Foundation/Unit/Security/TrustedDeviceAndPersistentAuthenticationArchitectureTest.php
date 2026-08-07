<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationCredentialStoreInterface;
use Sif\Foundation\Security\Contracts\TrustedDeviceGrantStoreInterface;
use Sif\Foundation\Security\Exceptions\InvalidPersistentAuthenticationCredentialException;
use Sif\Foundation\Security\Exceptions\InvalidTrustedDeviceGrantException;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationCredential;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationSelector;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationValidatorDigest;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrant;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantId;

final class TrustedDeviceAndPersistentAuthenticationArchitectureTest extends TestCase
{
    public function testPersistentCredentialSnapshotDoesNotExposeSelectorIdentityOrValidatorDigest(): void
    {
        $credential = new PersistentAuthenticationCredential(
            new PersistentAuthenticationSelector('selector-abcdef0123456789'),
            new IdentityId('user-1001'),
            new PersistentAuthenticationValidatorDigest(str_repeat('a', 64)),
            new DateTimeImmutable('2026-08-07T10:00:00-03:00'),
            new DateTimeImmutable('2026-09-06T10:00:00-03:00')
        );

        $snapshot = $credential->snapshot();
        $encoded = json_encode($snapshot, JSON_THROW_ON_ERROR);

        self::assertSame(64, strlen($snapshot['selector_fingerprint']));
        self::assertSame(64, strlen($snapshot['identity_fingerprint']));
        self::assertSame('active', $snapshot['status']);
        self::assertStringNotContainsString('selector-abcdef0123456789', $encoded);
        self::assertStringNotContainsString('user-1001', $encoded);
        self::assertStringNotContainsString(str_repeat('a', 64), $encoded);
    }

    public function testPersistentCredentialUsesAbsoluteExpirationBoundary(): void
    {
        $credential = new PersistentAuthenticationCredential(
            new PersistentAuthenticationSelector('selector-abcdef0123456789'),
            new IdentityId('user-1002'),
            new PersistentAuthenticationValidatorDigest(str_repeat('b', 64)),
            new DateTimeImmutable('2026-08-07T13:00:00Z'),
            new DateTimeImmutable('2026-08-08T13:00:00Z')
        );

        self::assertFalse($credential->isExpiredAt(new DateTimeImmutable('2026-08-08T12:59:59Z')));
        self::assertTrue($credential->isExpiredAt(new DateTimeImmutable('2026-08-08T13:00:00Z')));
    }

    public function testTrustedDeviceGrantIsSeparateFromPersistentAuthenticationCredential(): void
    {
        $grant = new TrustedDeviceGrant(
            new TrustedDeviceGrantId('trusted-device-abcdef012345'),
            new IdentityId('user-1003'),
            new DateTimeImmutable('2026-08-07T13:00:00Z'),
            new DateTimeImmutable('2026-08-14T13:00:00Z')
        );

        $snapshot = $grant->snapshot();

        self::assertSame('active', $snapshot['status']);
        self::assertArrayHasKey('id_fingerprint', $snapshot);
        self::assertArrayNotHasKey('selector', $snapshot);
        self::assertArrayNotHasKey('validator_digest', $snapshot);
        self::assertArrayNotHasKey('authentication_level', $snapshot);
    }

    public function testStoreContractsRemainPersistenceCookieSessionAndMfaNeutral(): void
    {
        foreach ([
            PersistentAuthenticationCredentialStoreInterface::class,
            TrustedDeviceGrantStoreInterface::class,
        ] as $contract) {
            $reflection = new \ReflectionClass($contract);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('BaseModel', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Cookie', $source);
            self::assertStringNotContainsString('Session', $source);
            self::assertStringNotContainsString('MultiFactor', $source);
            self::assertStringNotContainsString('Keycloak', $source);
        }
    }

    public function testInvalidValidityWindowsAreRejected(): void
    {
        $this->expectException(InvalidPersistentAuthenticationCredentialException::class);

        new PersistentAuthenticationCredential(
            new PersistentAuthenticationSelector('selector-abcdef0123456789'),
            new IdentityId('user-1004'),
            new PersistentAuthenticationValidatorDigest(str_repeat('c', 64)),
            new DateTimeImmutable('2026-08-07T13:00:00Z'),
            new DateTimeImmutable('2026-08-07T13:00:00Z')
        );
    }

    public function testInvalidTrustedDeviceWindowIsRejectedIndependently(): void
    {
        $this->expectException(InvalidTrustedDeviceGrantException::class);

        new TrustedDeviceGrant(
            new TrustedDeviceGrantId('trusted-device-abcdef012345'),
            new IdentityId('user-1005'),
            new DateTimeImmutable('2026-08-07T13:00:00Z'),
            new DateTimeImmutable('2026-08-07T12:59:59Z')
        );
    }
}
