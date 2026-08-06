<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\RequestBody;
use Sif\Foundation\Http\Value\Uri;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Authentication\AuthenticationOrchestrator;
use Sif\Foundation\Security\Authentication\AuthenticationRequest;
use Sif\Foundation\Security\Authentication\AuthenticatorId;
use Sif\Foundation\Security\Authentication\AuthenticatorRegistry;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Contracts\AuthenticatorInterface;
use Sif\Foundation\Security\Credentials\CredentialType;
use Sif\Foundation\Security\Http\Password\PasswordLoginEndpoint;
use Sif\Foundation\Security\Http\Password\PasswordLoginRequest;
use Sif\Foundation\Security\Http\Password\PasswordLogoutEndpoint;
use Sif\Foundation\Security\Http\Password\PasswordSessionLoginService;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\Results\AuthenticationFailureReason;
use Sif\Foundation\Security\Results\AuthenticationResult;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class PasswordHttpSessionIntegrationTest extends TestCase
{
    public function testLoginRequestRedactsPasswordAndRejectsSerialization(): void
    {
        $login = PasswordLoginRequest::fromJson('{"identity":"alice","password":"secret"}');

        self::assertSame('[REDACTED]', $login->__debugInfo()['password']);
        $this->expectException(\LogicException::class);
        serialize($login);
    }

    public function testSuccessfulLoginPersistsPrincipalAndRequestsSessionRegeneration(): void
    {
        [$endpoint, , $session, $context] = $this->fixture(true);
        $request = new Request(
            HttpMethod::Post,
            Uri::fromString('/login'),
            body: new RequestBody('{"identity":"alice","password":"secret"}', 'application/json', 'utf-8')
        );

        $response = $endpoint->handle($request, $session, new DateTimeImmutable('2026-08-06T12:00:00Z'));

        self::assertSame(200, $response->status()->code());
        self::assertTrue($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertTrue($session->regenerationRequested());
        self::assertTrue($context->principal()->isAuthenticated());
        self::assertSame('no-store', $response->headers()->line('Cache-Control'));
    }

    public function testFailedLoginReturnsGenericUnauthorizedResponseWithoutSessionMutation(): void
    {
        [$endpoint, , $session] = $this->fixture(false);
        $request = new Request(
            HttpMethod::Post,
            Uri::fromString('/login'),
            body: new RequestBody('{"identity":"alice","password":"wrong"}', 'application/json', 'utf-8')
        );

        $response = $endpoint->handle($request, $session, new DateTimeImmutable('2026-08-06T12:00:00Z'));

        self::assertSame(401, $response->status()->code());
        self::assertFalse($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertSame('Password realm="application"', $response->headers()->line('WWW-Authenticate'));
        self::assertStringNotContainsString('wrong', $response->body()->contents());
    }

    public function testLogoutClearsPrincipalAndRequestsRegeneration(): void
    {
        [$endpoint, $logout, $session, $context] = $this->fixture(true);
        $request = new Request(
            HttpMethod::Post,
            Uri::fromString('/login'),
            body: new RequestBody('{"identity":"alice","password":"secret"}', 'application/json', 'utf-8')
        );
        $endpoint->handle($request, $session, new DateTimeImmutable('2026-08-06T12:00:00Z'));

        $response = $logout->handle($session);

        self::assertSame(200, $response->status()->code());
        self::assertFalse($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertFalse($context->principal()->isAuthenticated());
        self::assertTrue($session->regenerationRequested());
    }

    /** @return array{PasswordLoginEndpoint, PasswordLogoutEndpoint, SessionState, SecurityContext} */
    private function fixture(bool $success): array
    {
        $registry = new AuthenticatorRegistry();
        $registry->register(new class($success) implements AuthenticatorInterface {
            public function __construct(private readonly bool $success) {}
            public function id(): AuthenticatorId { return new AuthenticatorId('test-password'); }
            public function supportedCredentialTypes(): array { return [new CredentialType('password')]; }
            public function authenticate(AuthenticationRequest $request): AuthenticationResult
            {
                if (!$this->success) {
                    return AuthenticationResult::failed(AuthenticationFailureReason::InvalidCredentials);
                }

                return AuthenticationResult::succeeded(new AuthenticatedPrincipal(
                    new Identity(new IdentityId('user-1')),
                    new PrincipalAttributeCollection(),
                    new AuthenticationEvidence(
                        new AuthenticationMethod('password'),
                        new AuthenticationLevel(20),
                        $request->requestedAt()
                    )
                ));
            }
        });

        $context = new SecurityContext();
        $service = new PasswordSessionLoginService(
            new AuthenticationOrchestrator($registry),
            new SessionAuthenticationManager(),
            $context
        );
        $now = new DateTimeImmutable('2026-08-06T12:00:00Z');
        $session = new SessionState(new SessionId(str_repeat('a', 32)), [], $now, $now);

        return [new PasswordLoginEndpoint($service), new PasswordLogoutEndpoint($service), $session, $context];
    }
}
