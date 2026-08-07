<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\TrustedDevice\DefaultTrustedDevicePolicy;
use Sif\Foundation\Security\TrustedDevice\InMemoryTrustedDeviceGrantStore;
use Sif\Foundation\Security\TrustedDevice\SecureTrustedDeviceGrantIdGenerator;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantService;
use Sif\Foundation\Security\TrustedDevice\TrustedDevicePolicyService;

final class TrustedDevicePolicyAndMfaInteractionTest extends TestCase
{
    public function testDefaultPolicyNeverSkipsMfaImplicitly(): void
    {
        [$policyService, $grant] = $this->fixture();

        $decision = $policyService->evaluate(
            $this->principal(50),
            $grant->id(),
            new AuthenticationLevel(70),
            $this->now()->modify('+1 hour')
        );

        self::assertTrue($decision->isTrusted());
        self::assertFalse($decision->mfaMayBeSkipped());
    }

    public function testAlreadyStrongAuthenticationRemainsStrongWithoutGrantDrivenElevation(): void
    {
        [$policyService, $grant] = $this->fixture();

        $principal = $this->principal(80);

        $decision = $policyService->evaluate(
            $principal,
            $grant->id(),
            new AuthenticationLevel(70),
            $this->now()->modify('+1 hour')
        );

        self::assertTrue($decision->isTrusted());
        self::assertFalse($decision->mfaMayBeSkipped());
        self::assertSame(80, $principal->evidence()->level()->value());
    }

    public function testGrantForAnotherIdentityIsRejected(): void
    {
        [$policyService, $grant] = $this->fixture();

        $decision = $policyService->evaluate(
            $this->principal(50, 'other-user'),
            $grant->id(),
            new AuthenticationLevel(70),
            $this->now()->modify('+1 hour')
        );

        self::assertFalse($decision->isTrusted());
        self::assertFalse($decision->mfaMayBeSkipped());
    }

    public function testExpiredGrantIsRejected(): void
    {
        [$policyService, $grant] = $this->fixture();

        $decision = $policyService->evaluate(
            $this->principal(50),
            $grant->id(),
            new AuthenticationLevel(70),
            $this->now()->modify('+8 days')
        );

        self::assertFalse($decision->isTrusted());
    }

    public function testUnknownGrantIsRejectedWithoutAuthenticationMutation(): void
    {
        [$policyService] = $this->fixture();
        $principal = $this->principal(50);

        $decision = $policyService->evaluate(
            $principal,
            new \Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantId(
                'unknown-device-abcdef012345'
            ),
            new AuthenticationLevel(70),
            $this->now()
        );

        self::assertFalse($decision->isTrusted());
        self::assertSame(50, $principal->evidence()->level()->value());
    }

    public function testDefaultPolicyHasNoSessionOrSecurityContextDependencies(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/TrustedDevice/DefaultTrustedDevicePolicy.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('SessionAuthenticationManager', $source);
        self::assertStringNotContainsString('SecurityContext', $source);
        self::assertStringNotContainsString('MultiFactorChallenge', $source);
        self::assertStringNotContainsString('authenticate(', $source);
    }

    /** @return array{TrustedDevicePolicyService,\Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrant} */
    private function fixture(): array
    {
        $store = new InMemoryTrustedDeviceGrantStore();
        $grantService = new TrustedDeviceGrantService(
            $store,
            new SecureTrustedDeviceGrantIdGenerator()
        );

        $grant = $grantService->issue(
            new IdentityId('policy-user'),
            $this->now(),
            $this->now()->modify('+7 days')
        );

        return [
            new TrustedDevicePolicyService(
                $store,
                new DefaultTrustedDevicePolicy()
            ),
            $grant,
        ];
    }

    private function principal(
        int $level,
        string $identityId = 'policy-user'
    ): AuthenticatedPrincipal {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId($identityId)),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel($level),
                $this->now()
            )
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T15:00:00+00:00');
    }
}
