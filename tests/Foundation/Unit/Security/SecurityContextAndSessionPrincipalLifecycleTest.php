<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttribute;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class SecurityContextAndSessionPrincipalLifecycleTest extends TestCase
{
    public function testContextStartsAnonymous(): void
    {
        $context = new SecurityContext();
        self::assertFalse($context->isAuthenticated());
        self::assertFalse($context->principal()->isAuthenticated());
    }

    public function testAuthenticationStoresSnapshotAndRequestsRegeneration(): void
    {
        $session = $this->session();
        $context = new SecurityContext();
        $principal = $this->principal();

        (new SessionAuthenticationManager())->authenticate($principal, $session, $context);

        self::assertTrue($context->isAuthenticated());
        self::assertTrue($session->regenerationRequested());
        self::assertIsArray($session->get(SessionAuthenticationManager::SESSION_KEY));
    }

    public function testPrincipalCanBeRestoredFromSession(): void
    {
        $session = $this->session();
        $manager = new SessionAuthenticationManager();
        $manager->authenticate($this->principal(), $session, new SecurityContext());

        $restored = new SecurityContext();
        $manager->restore($session, $restored);

        self::assertTrue($restored->isAuthenticated());
        self::assertInstanceOf(AuthenticatedPrincipal::class, $restored->principal());
        self::assertSame('user-42', $restored->principal()->identity()->id()->value());
    }

    public function testMalformedSnapshotFailsClosedAndIsRemoved(): void
    {
        $session = $this->session();
        $session->put(SessionAuthenticationManager::SESSION_KEY, ['version' => 999]);
        $context = new SecurityContext($this->principal());

        (new SessionAuthenticationManager())->restore($session, $context);

        self::assertFalse($context->isAuthenticated());
        self::assertFalse($session->has(SessionAuthenticationManager::SESSION_KEY));
    }

    public function testLogoutClearsPrincipalAndRequestsRegeneration(): void
    {
        $session = $this->session();
        $context = new SecurityContext($this->principal());
        $session->put(SessionAuthenticationManager::SESSION_KEY, ['placeholder' => true]);

        (new SessionAuthenticationManager())->logout($session, $context);

        self::assertFalse($context->isAuthenticated());
        self::assertFalse($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertTrue($session->regenerationRequested());
    }

    private function session(): SessionState
    {
        $now = new DateTimeImmutable('2026-08-05T12:00:00+00:00');
        return new SessionState(new SessionId(str_repeat('a', 64)), [], $now, $now, new: true);
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('user-42')),
            new PrincipalAttributeCollection(new PrincipalAttribute('tenant', 'central')),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new DateTimeImmutable('2026-08-05T11:00:00+00:00'),
            ),
        );
    }
}
