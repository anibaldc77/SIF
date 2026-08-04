<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\RequestBody;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;
use Sif\Foundation\Security\Csrf\CsrfMiddleware;
use Sif\Foundation\Security\Csrf\CsrfRequestTokenExtractor;
use Sif\Foundation\Security\Csrf\CsrfTokenManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionRequestAttributes;
use Sif\Foundation\Session\SessionState;

final class CsrfTokenValidationMiddlewareTest extends TestCase
{
    public function testValidatesHeaderAndBodyTokensAndRejectsInvalidRequests(): void
    {
        $state = $this->state();
        $manager = new CsrfTokenManager();
        $token = $manager->token($state)->value();
        $middleware = new CsrfMiddleware($manager, new CsrfRequestTokenExtractor());
        $handler = new CsrfRecordingHandler();

        $headerRequest = $this->request(HttpMethod::Post, new HeaderBag(['X-CSRF-Token' => $token]))
            ->withAttribute(SessionRequestAttributes::STATE, $state);
        self::assertSame(200, $middleware->process($headerRequest, $handler)->status()->code());

        $bodyRequest = $this->request(
            HttpMethod::Post,
            body: new RequestBody('_csrf=' . rawurlencode($token), 'application/x-www-form-urlencoded'),
        )->withAttribute(SessionRequestAttributes::STATE, $state);
        self::assertSame(200, $middleware->process($bodyRequest, $handler)->status()->code());

        $invalid = $this->request(HttpMethod::Post, new HeaderBag(['X-CSRF-Token' => str_repeat('x', 43)]))
            ->withAttribute(SessionRequestAttributes::STATE, $state);
        $response = $middleware->process($invalid, $handler);
        self::assertSame(403, $response->status()->code());
        self::assertSame('application/problem+json', $response->body()->mediaType());
        self::assertStringNotContainsString($token, $response->body()->contents());
    }

    public function testSafeMethodsBypassValidationAndProtectedMethodsRequireSession(): void
    {
        $middleware = new CsrfMiddleware();
        $handler = new CsrfRecordingHandler();

        self::assertSame(200, $middleware->process($this->request(HttpMethod::Get), $handler)->status()->code());
        self::assertSame(403, $middleware->process($this->request(HttpMethod::Delete), $handler)->status()->code());
        self::assertSame(1, $handler->calls);
    }

    public function testHandlerExceptionsPropagateAfterSuccessfulValidation(): void
    {
        $state = $this->state();
        $manager = new CsrfTokenManager();
        $token = $manager->token($state)->value();
        $request = $this->request(HttpMethod::Post, new HeaderBag(['X-CSRF-Token' => $token]))
            ->withAttribute(SessionRequestAttributes::STATE, $state);

        $this->expectException(\RuntimeException::class);
        (new CsrfMiddleware($manager))->process($request, new CsrfThrowingHandler());
    }

    private function request(
        HttpMethod $method,
        HeaderBag $headers = new HeaderBag(),
        RequestBody $body = new RequestBody(),
    ): Request {
        return new Request($method, Uri::fromString('https://example.test/form'), headers: $headers, body: $body);
    }

    private function state(): SessionState
    {
        $now = new \DateTimeImmutable('2026-08-03T18:00:00+00:00');
        return new SessionState(new SessionId(str_repeat('a', 43)), [], $now, $now, new: true);
    }
}

final class CsrfRecordingHandler implements RequestHandlerInterface
{
    public int $calls = 0;

    public function handle(RequestInterface $request): ResponseInterface
    {
        ++$this->calls;
        return Response::json(['ok' => true]);
    }
}

final class CsrfThrowingHandler implements RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        throw new \RuntimeException('handler failed');
    }
}
