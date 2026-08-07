<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\TrustedDevice\InMemoryTrustedDeviceGrantStore;
use Sif\Foundation\Security\TrustedDevice\SecureTrustedDeviceGrantIdGenerator;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantService;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantStatus;

final class TrustedDeviceGrantLifecycleAndRevocationTest extends TestCase
{
    public function testIssueCreatesActiveGrantWithoutAuthenticationLevel(): void
    {
        [$service] = $this->service();
        $now = $this->now();

        $grant = $service->issue(
            new IdentityId('trusted-user-1'),
            $now,
            $now->modify('+14 days')
        );

        $snapshot = $grant->snapshot();

        self::assertSame(TrustedDeviceGrantStatus::Active, $grant->status());
        self::assertFalse($snapshot['revoked']);
        self::assertArrayNotHasKey('authentication_level', $snapshot);
        self::assertArrayNotHasKey('mfa_satisfied', $snapshot);
        self::assertArrayNotHasKey('persistent_authentication', $snapshot);
    }

    public function testGrantIsTrustedOnlyForItsIdentityAndWhileUsable(): void
    {
        [$service] = $this->service();
        $now = $this->now();

        $grant = $service->issue(
            new IdentityId('trusted-user-2'),
            $now,
            $now->modify('+1 day')
        );

        self::assertTrue(
            $service->isTrusted(
                $grant->id(),
                new IdentityId('trusted-user-2'),
                $now->modify('+1 hour')
            )
        );

        self::assertFalse(
            $service->isTrusted(
                $grant->id(),
                new IdentityId('different-user'),
                $now->modify('+1 hour')
            )
        );

        self::assertFalse(
            $service->isTrusted(
                $grant->id(),
                new IdentityId('trusted-user-2'),
                $now->modify('+1 day')
            )
        );
    }

    public function testIndividualRevocationMakesGrantUnusable(): void
    {
        [$service, $store] = $this->service();
        $now = $this->now();

        $grant = $service->issue(
            new IdentityId('trusted-user-3'),
            $now,
            $now->modify('+14 days')
        );

        self::assertTrue(
            $service->revoke($grant->id(), $now->modify('+1 hour'))
        );

        self::assertFalse(
            $service->isTrusted(
                $grant->id(),
                new IdentityId('trusted-user-3'),
                $now->modify('+2 hours')
            )
        );

        self::assertSame(
            TrustedDeviceGrantStatus::Revoked,
            $store->find($grant->id())?->status()
        );
    }

    public function testGlobalRevocationAffectsOnlyTargetIdentity(): void
    {
        [$service] = $this->service();
        $now = $this->now();

        $first = $service->issue(
            new IdentityId('trusted-user-4'),
            $now,
            $now->modify('+14 days')
        );
        $second = $service->issue(
            new IdentityId('trusted-user-4'),
            $now,
            $now->modify('+14 days')
        );
        $other = $service->issue(
            new IdentityId('trusted-user-other'),
            $now,
            $now->modify('+14 days')
        );

        self::assertSame(
            2,
            $service->revokeAllForIdentity(
                new IdentityId('trusted-user-4'),
                $now->modify('+1 hour')
            )
        );

        self::assertFalse(
            $service->isTrusted(
                $first->id(),
                new IdentityId('trusted-user-4'),
                $now->modify('+2 hours')
            )
        );
        self::assertFalse(
            $service->isTrusted(
                $second->id(),
                new IdentityId('trusted-user-4'),
                $now->modify('+2 hours')
            )
        );
        self::assertTrue(
            $service->isTrusted(
                $other->id(),
                new IdentityId('trusted-user-other'),
                $now->modify('+2 hours')
            )
        );
    }

    public function testGrantIdentifiersAreGeneratedWithCryptographicEntropy(): void
    {
        $generator = new SecureTrustedDeviceGrantIdGenerator();

        $first = $generator->generate();
        $second = $generator->generate();

        self::assertNotSame($first->value(), $second->value());
        self::assertSame(32, strlen($first->value()));
    }

    public function testTrustedDeviceGrantDoesNotAuthenticateOrMutateSecurityContext(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/TrustedDevice/TrustedDeviceGrantService.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('AuthenticatedPrincipal', $source);
        self::assertStringNotContainsString('SecurityContext', $source);
        self::assertStringNotContainsString('SessionAuthenticationManager', $source);
        self::assertStringNotContainsString('AuthenticationLevel', $source);
        self::assertStringNotContainsString('MultiFactor', $source);
    }

    /** @return array{TrustedDeviceGrantService,InMemoryTrustedDeviceGrantStore} */
    private function service(): array
    {
        $store = new InMemoryTrustedDeviceGrantStore();

        return [
            new TrustedDeviceGrantService(
                $store,
                new SecureTrustedDeviceGrantIdGenerator()
            ),
            $store,
        ];
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T13:00:00+00:00');
    }
}
