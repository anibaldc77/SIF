<?php

declare(strict_types=1);

namespace Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Http\SecurityContextMiddleware;
use Sif\Foundation\Security\Http\SecurityHttpResponseFactory;
use Sif\Foundation\Security\Http\SecurityRequestAttributes;

final class HttpControllerCliSecurityIntegrationTest extends TestCase
{
    public function testSecurityContextMiddlewareExposesPrincipalAsRequestAttribute(): void
    {
        $middleware = new SecurityContextMiddleware(new SecurityContext());
        $handler = new class implements RequestHandlerInterface {
            public function handle(RequestInterface $request): ResponseInterface
            {
                $principal = $request->attributes()->get(SecurityRequestAttributes::PRINCIPAL);
                TestCase::assertNotNull($principal);

                return Response::text('ok');
            }
        };

        $response = $middleware->process(
            new Request(HttpMethod::Get, Uri::fromString('https://example.test/secure')),
            $handler,
        );

        self::assertSame(200, $response->status()->code());
    }

    public function testUnauthorizedResponseCarriesChallengeWithoutSensitiveDetails(): void
    {
        $response = (new SecurityHttpResponseFactory())->unauthorized('Bearer realm="api"');

        self::assertSame(401, $response->status()->code());
        self::assertSame(['Bearer realm="api"'], $response->headers()->values('WWW-Authenticate'));
        self::assertStringNotContainsString('token', strtolower($response->body()->contents()));
    }

    public function testForbiddenResponseDoesNotAdvertiseAuthenticationChallenge(): void
    {
        $response = (new SecurityHttpResponseFactory())->forbidden();

        self::assertSame(403, $response->status()->code());
        self::assertFalse($response->headers()->has('WWW-Authenticate'));
    }
}
