<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationPrincipalFactoryInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\PersistentAuthentication\InMemoryPersistentAuthenticationCredentialStore;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationService;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationToken;
use Sif\Foundation\Security\PersistentAuthentication\PersistentSessionRestorationService;
use Sif\Foundation\Security\PersistentAuthentication\PersistentSessionRestorationStatus;
use Sif\Foundation\Security\PersistentAuthentication\SecurePersistentAuthenticationTokenGenerator;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class PersistentAuthenticationSessionRestorationTest extends TestCase
{
    public function testValidPersistentCredentialRestoresSessionAndRotatesToken(): void
    {
        [$service, $issuer] = $this->service();
        $now = $this->now();
        $token = $issuer->issue(
            new IdentityId('persistent-user-1'),
            $now,
            $now->modify('+30 days')
        );

        $session = $this->session($now);
        $context = new SecurityContext();

        $result = $service->restore(
            $token,
            $session,
            $context,
            $now->modify('+1 hour')
        );

        self::assertTrue($result->isRestored());
        self::assertTrue($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertTrue($session->regenerationRequested());

        $principal = $result->principal();
        self::assertNotNull($principal);
        self::assertSame('persistent-user-1', $principal->identity()->id()->value());
        self::assertSame('persistent', $principal->evidence()->method()->value());
        self::assertSame(20, $principal->evidence()->level()->value());

        $replacement = $result->replacementToken();
        self::assertNotNull($replacement);
        self::assertTrue($replacement->selector()->equals($token->selector()));
        self::assertFalse(
            $replacement->validatorDigest()->equals($token->validatorDigest())
        );
    }

    public function testOldTokenCannotRestoreSecondSessionAfterRotation(): void
    {
        [$service, $issuer] = $this->service();
        $now = $this->now();
        $token = $issuer->issue(
            new IdentityId('persistent-user-2'),
            $now,
            $now->modify('+30 days')
        );

        self::assertTrue(
            $service->restore(
                $token,
                $this->session($now),
                new SecurityContext(),
                $now->modify('+1 hour')
            )->isRestored()
        );

        $replay = $service->restore(
            $token,
            $this->session($now),
            new SecurityContext(),
            $now->modify('+2 hours')
        );

        self::assertSame(
            PersistentSessionRestorationStatus::ReplaySuspected,
            $replay->status()
        );
    }

    public function testMissingIdentityRevokesCredentialFailClosed(): void
    {
        $store = new InMemoryPersistentAuthenticationCredentialStore();
        $issuer = new PersistentAuthenticationService(
            $store,
            new SecurePersistentAuthenticationTokenGenerator()
        );
        $now = $this->now();
        $token = $issuer->issue(
            new IdentityId('missing-user'),
            $now,
            $now->modify('+30 days')
        );

        $service = new PersistentSessionRestorationService(
            $store,
            $issuer,
            new class implements PersistentAuthenticationPrincipalFactoryInterface {
                public function create(
                    IdentityId $identityId,
                    AuthenticationEvidence $evidence
                ): ?AuthenticatedPrincipal {
                    return null;
                }
            },
            new SessionAuthenticationManager(),
            new AuthenticationLevel(20)
        );

        $result = $service->restore(
            $token,
            $this->session($now),
            new SecurityContext(),
            $now->modify('+1 hour')
        );

        self::assertSame(
            PersistentSessionRestorationStatus::IdentityUnavailable,
            $result->status()
        );

        $credential = $store->findBySelector($token->selector());
        self::assertNotNull($credential);
        self::assertSame('revoked', $credential->status()->value);
    }

    public function testExistingAuthenticatedPrincipalIsNeverOverwritten(): void
    {
        [$service, $issuer] = $this->service();
        $now = $this->now();
        $token = $issuer->issue(
            new IdentityId('persistent-user-3'),
            $now,
            $now->modify('+30 days')
        );

        $alreadyAuthenticated = new AuthenticatedPrincipal(
            new Identity(new IdentityId('existing-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new \Sif\Foundation\Security\Authentication\AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                $now
            )
        );

        $context = new SecurityContext($alreadyAuthenticated);
        $result = $service->restore(
            $token,
            $this->session($now),
            $context,
            $now->modify('+1 hour')
        );

        self::assertSame(
            PersistentSessionRestorationStatus::IdentityUnavailable,
            $result->status()
        );
        $currentPrincipal = $context->principal();
        self::assertInstanceOf(AuthenticatedPrincipal::class, $currentPrincipal);
        self::assertSame(
            'existing-user',
            $currentPrincipal->identity()->id()->value()
        );
    }

    public function testRestoredAuthenticationDoesNotImplyMfaOrTrustedDevice(): void
    {
        [$service, $issuer] = $this->service();
        $now = $this->now();
        $token = $issuer->issue(
            new IdentityId('persistent-user-4'),
            $now,
            $now->modify('+30 days')
        );

        $result = $service->restore(
            $token,
            $this->session($now),
            new SecurityContext(),
            $now->modify('+1 hour')
        );

        $principal = $result->principal();
        self::assertNotNull($principal);
        self::assertSame('persistent', $principal->evidence()->method()->value());
        self::assertLessThan(70, $principal->evidence()->level()->value());

        $source = file_get_contents(
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/PersistentAuthentication/'
            . 'PersistentSessionRestorationService.php'
        );
        self::assertIsString($source);
        self::assertStringNotContainsString('TrustedDeviceGrantService', $source);
        self::assertStringNotContainsString('MultiFactor', $source);
    }

    /** @return array{PersistentSessionRestorationService,PersistentAuthenticationService} */
    private function service(): array
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

        return [
            new PersistentSessionRestorationService(
                $store,
                $issuer,
                $factory,
                new SessionAuthenticationManager(),
                new AuthenticationLevel(20)
            ),
            $issuer,
        ];
    }

    private function session(DateTimeImmutable $now): SessionState
    {
        return new SessionState(
            new SessionId(str_repeat('d', 32)),
            [],
            $now,
            $now
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T14:00:00+00:00');
    }
}
