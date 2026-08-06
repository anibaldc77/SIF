<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Authorization\AuthorizationAction;
use Sif\Foundation\Security\Authorization\AuthorizationDecision;
use Sif\Foundation\Security\Authorization\AuthorizationManager;
use Sif\Foundation\Security\Authorization\AuthorizationPolicyId;
use Sif\Foundation\Security\Authorization\AuthorizationPolicyRegistry;
use Sif\Foundation\Security\Authorization\AuthorizationRequest;
use Sif\Foundation\Security\Authorization\AuthorizationResource;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Contracts\AuthorizationPolicyInterface;
use Sif\Foundation\Security\Controller\AuthorizationRequirement;
use Sif\Foundation\Security\Http\AuthorizationMiddleware;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class AuthenticationAuthorizationProductCompletionTest extends TestCase
{
    public function testAuthenticatedSessionPrincipalCanReachAuthorizedHttpHandler(): void
    {
        $session = $this->session();
        $context = new SecurityContext();
        $principal = $this->principal();
        $sessions = new SessionAuthenticationManager();
        $sessions->authenticate($principal, $session, $context);

        $restored = new SecurityContext();
        $sessions->restore($session, $restored);

        $policy = new class implements AuthorizationPolicyInterface {
            public function id(): AuthorizationPolicyId { return new AuthorizationPolicyId('allow-report-read'); }
            public function supports(AuthorizationRequest $request): bool { return $request->action()->value() === 'report.read'; }
            public function decide(AuthorizationRequest $request): AuthorizationDecision { return AuthorizationDecision::allow(); }
        };

        $registry = new AuthorizationPolicyRegistry();
        $registry->register($policy);

        $middleware = new AuthorizationMiddleware(
            $restored,
            new AuthorizationManager($registry),
            new AuthorizationRequirement(
                new AuthorizationAction('report.read'),
                new AuthorizationResource('report', '42'),
            ),
        );

        $response = $middleware->process($this->request(), new class implements RequestHandlerInterface {
            public function handle(RequestInterface $request): ResponseInterface { return Response::text('authorized'); }
        });

        self::assertSame(200, $response->status()->code());
        self::assertSame('authorized', $response->body()->contents());
    }

    public function testAnonymousRequestFailsClosedWithAuthenticationChallenge(): void
    {
        $middleware = new AuthorizationMiddleware(
            new SecurityContext(),
            new AuthorizationManager(new AuthorizationPolicyRegistry()),
            new AuthorizationRequirement(
                new AuthorizationAction('report.read'),
                new AuthorizationResource('report', '42'),
            ),
        );

        $response = $middleware->process($this->request(), new class implements RequestHandlerInterface {
            public function handle(RequestInterface $request): ResponseInterface { return Response::text('should-not-run'); }
        });

        self::assertSame(401, $response->status()->code());
        self::assertTrue($response->headers()->has('WWW-Authenticate'));
        self::assertStringNotContainsString('credential', strtolower($response->body()->contents()));
    }

    public function testCorruptedSessionSnapshotCannotRestoreAuthentication(): void
    {
        $session = $this->session();
        $session->put(SessionAuthenticationManager::SESSION_KEY, [
            'version' => 1,
            'identity_id' => "invalid\0identity",
            'attributes' => [],
            'authentication' => [
                'method' => 'password',
                'level' => 50,
                'authenticated_at' => '2026-08-06T10:00:00+00:00',
            ],
        ]);
        $context = new SecurityContext($this->principal());

        (new SessionAuthenticationManager())->restore($session, $context);

        self::assertFalse($context->isAuthenticated());
        self::assertFalse($session->has(SessionAuthenticationManager::SESSION_KEY));
    }

    private function request(): Request
    {
        return new Request(HttpMethod::Get, Uri::fromString('https://example.test/reports/42'));
    }

    private function session(): SessionState
    {
        $now = new DateTimeImmutable('2026-08-06T10:00:00+00:00');
        return new SessionState(new SessionId(str_repeat('b', 64)), [], $now, $now, new: true);
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('user-42')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                new DateTimeImmutable('2026-08-06T09:55:00+00:00'),
            ),
        );
    }
}
