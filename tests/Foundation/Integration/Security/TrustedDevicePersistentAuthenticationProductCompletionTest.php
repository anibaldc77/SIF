<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationPrincipalFactoryInterface;
use Sif\Foundation\Security\Http\PersistentAuthentication\PersistentAuthenticationCookiePayload;
use Sif\Foundation\Security\Http\PersistentAuthentication\PersistentAuthenticationHttpRestorationService;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\PersistentAuthentication\InMemoryPersistentAuthenticationCredentialStore;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationService;
use Sif\Foundation\Security\PersistentAuthentication\PersistentSessionRestorationService;
use Sif\Foundation\Security\PersistentAuthentication\PersistentSessionRestorationStatus;
use Sif\Foundation\Security\PersistentAuthentication\SecurePersistentAuthenticationTokenGenerator;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Security\TrustedDevice\DefaultTrustedDevicePolicy;
use Sif\Foundation\Security\TrustedDevice\InMemoryTrustedDeviceGrantStore;
use Sif\Foundation\Security\TrustedDevice\SecureTrustedDeviceGrantIdGenerator;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantService;
use Sif\Foundation\Security\TrustedDevice\TrustedDevicePolicyService;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class TrustedDevicePersistentAuthenticationProductCompletionTest extends TestCase
{
    public function testPersistentAuthenticationRestoresSessionRotatesCookieAndRejectsReplay(): void
    {
        [$http, $issuer] = $this->persistentFixture();
        $now = $this->now();

        $token = $issuer->issue(
            new IdentityId('product-persistent-user'),
            $now,
            $now->modify('+30 days')
        );

        $cookie = PersistentAuthenticationCookiePayload::fromToken($token);
        $raw = $cookie->expose(static fn (string $value): string => $value);

        $session = $this->session($now);
        $context = new SecurityContext();

        $restored = $http->restoreFromCookie(
            $raw,
            $session,
            $context,
            $now->modify('+1 hour')
        );

        self::assertTrue($restored->isRestored());
        self::assertTrue($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertTrue($session->regenerationRequested());

        $principal = $context->principal();
        self::assertInstanceOf(AuthenticatedPrincipal::class, $principal);
        self::assertSame(
            'product-persistent-user',
            $principal->identity()->id()->value()
        );
        self::assertSame(
            'persistent',
            $principal->evidence()->method()->value()
        );
        self::assertSame(20, $principal->evidence()->level()->value());

        $replacement = $http->replacementCookie($restored);
        self::assertNotNull($replacement);
        $replacementRaw = $replacement->expose(
            static fn (string $value): string => $value
        );
        self::assertNotSame($raw, $replacementRaw);

        $replay = $http->restoreFromCookie(
            $raw,
            $this->session($now),
            new SecurityContext(),
            $now->modify('+2 hours')
        );

        self::assertSame(
            PersistentSessionRestorationStatus::ReplaySuspected,
            $replay->status()
        );
    }

    public function testRotatedCookieCanRestoreNextSessionWhileAbsoluteExpiryRemainsBounded(): void
    {
        [$http, $issuer, $store] = $this->persistentFixture();
        $now = $this->now();
        $expiresAt = $now->modify('+7 days');

        $token = $issuer->issue(
            new IdentityId('rotation-chain-user'),
            $now,
            $expiresAt
        );

        $raw = PersistentAuthenticationCookiePayload::fromToken($token)->expose(
            static fn (string $value): string => $value
        );

        $first = $http->restoreFromCookie(
            $raw,
            $this->session($now),
            new SecurityContext(),
            $now->modify('+1 hour')
        );
        self::assertTrue($first->isRestored());

        $replacement = $http->replacementCookie($first);
        self::assertNotNull($replacement);
        $replacementRaw = $replacement->expose(
            static fn (string $value): string => $value
        );

        $second = $http->restoreFromCookie(
            $replacementRaw,
            $this->session($now),
            new SecurityContext(),
            $now->modify('+2 hours')
        );
        self::assertTrue($second->isRestored());

        $credential = $store->findBySelector($token->selector());
        self::assertNotNull($credential);
        self::assertSame(
            $expiresAt->format(DATE_ATOM),
            $credential->absoluteExpiresAt()->format(DATE_ATOM)
        );
    }

    public function testTrustedDeviceRemainsPolicySignalAndNeverElevatesAuthentication(): void
    {
        $now = $this->now();
        $store = new InMemoryTrustedDeviceGrantStore();
        $grantService = new TrustedDeviceGrantService(
            $store,
            new SecureTrustedDeviceGrantIdGenerator()
        );
        $grant = $grantService->issue(
            new IdentityId('trusted-product-user'),
            $now,
            $now->modify('+14 days')
        );

        $principal = new AuthenticatedPrincipal(
            new Identity(new IdentityId('trusted-product-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('persistent'),
                new AuthenticationLevel(20),
                $now
            )
        );

        $decision = (new TrustedDevicePolicyService(
            $store,
            new DefaultTrustedDevicePolicy()
        ))->evaluate(
            $principal,
            $grant->id(),
            new AuthenticationLevel(70),
            $now->modify('+1 hour')
        );

        self::assertTrue($decision->isTrusted());
        self::assertFalse($decision->mfaMayBeSkipped());
        self::assertSame(20, $principal->evidence()->level()->value());
    }

    public function testGlobalTrustedDeviceRevocationDoesNotTouchPersistentCredential(): void
    {
        [$http, $issuer] = $this->persistentFixture();
        $now = $this->now();
        $identity = new IdentityId('independent-revocation-user');

        $token = $issuer->issue(
            $identity,
            $now,
            $now->modify('+30 days')
        );

        $trustedStore = new InMemoryTrustedDeviceGrantStore();
        $trusted = new TrustedDeviceGrantService(
            $trustedStore,
            new SecureTrustedDeviceGrantIdGenerator()
        );
        $trusted->issue(
            $identity,
            $now,
            $now->modify('+7 days')
        );

        self::assertSame(
            1,
            $trusted->revokeAllForIdentity(
                $identity,
                $now->modify('+1 hour')
            )
        );

        $raw = PersistentAuthenticationCookiePayload::fromToken($token)->expose(
            static fn (string $value): string => $value
        );

        self::assertTrue(
            $http->restoreFromCookie(
                $raw,
                $this->session($now),
                new SecurityContext(),
                $now->modify('+2 hours')
            )->isRestored()
        );
    }

    /** @return array{PersistentAuthenticationHttpRestorationService,PersistentAuthenticationService,InMemoryPersistentAuthenticationCredentialStore} */
    private function persistentFixture(): array
    {
        $store = new InMemoryPersistentAuthenticationCredentialStore();
        $issuer = new PersistentAuthenticationService(
            $store,
            new SecurePersistentAuthenticationTokenGenerator()
        );

        $factory = new class implements PersistentAuthenticationPrincipalFactoryInterface {
            public function create(
                IdentityId $identityId,
                AuthenticationEvidence $evidence
            ): ?AuthenticatedPrincipal {
                return new AuthenticatedPrincipal(
                    new Identity($identityId),
                    new PrincipalAttributeCollection(),
                    $evidence
                );
            }
        };

        $restoration = new PersistentSessionRestorationService(
            $store,
            $issuer,
            $factory,
            new SessionAuthenticationManager(),
            new AuthenticationLevel(20)
        );

        return [
            new PersistentAuthenticationHttpRestorationService($restoration),
            $issuer,
            $store,
        ];
    }

    private function session(DateTimeImmutable $now): SessionState
    {
        return new SessionState(
            new SessionId(str_repeat('f', 32)),
            [],
            $now,
            $now
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T17:00:00+00:00');
    }
}
