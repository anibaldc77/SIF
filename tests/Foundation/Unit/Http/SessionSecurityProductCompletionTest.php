<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\CookieBag;
use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;
use Sif\Foundation\Security\Csrf\CsrfMiddleware;
use Sif\Foundation\Security\Csrf\CsrfTokenManager;
use Sif\Foundation\Session\Contracts\SessionIdGeneratorInterface;
use Sif\Foundation\Session\Middleware\SessionMiddleware;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionRequestAttributes;
use Sif\Foundation\Session\SessionRuntime;
use Sif\Foundation\Session\SessionState;
use Sif\Foundation\Session\Storage\InMemorySessionStore;

final class SessionSecurityProductCompletionTest extends TestCase
{
    public function testHttpApplicationsRemainCompatibleWithoutSessionComposition(): void
    {
        $handler = new SessionProductPlainHandler();
        $response = $handler->handle(new Request(HttpMethod::Get, Uri::fromString('https://example.test/health')));

        self::assertSame(200, $response->status()->code());
        self::assertSame([], $response->headers()->values('Set-Cookie'));
    }

    public function testSessionMiddlewarePersistsAndReopensStateExplicitly(): void
    {
        $store = new InMemorySessionStore();
        $middleware = new SessionMiddleware(new SessionRuntime(
            $store,
            new ProductCompletionSessionIdGenerator(),
            new ProductCompletionClock(),
        ));

        $created = $middleware->process($this->request(), new SessionProductCallbackHandler(
            static function (SessionState $state): ResponseInterface {
                $state->put('completed', true);
                return Response::json(['stored' => true]);
            },
        ));
        $identifier = $this->identifier($created);

        $reopened = $middleware->process($this->request($identifier), new SessionProductCallbackHandler(
            static function (SessionState $state): ResponseInterface {
                return Response::json(['completed' => $state->get('completed')]);
            },
        ));

        self::assertStringContainsString('"completed":true', $reopened->body()->contents());
        self::assertSame([], $reopened->headers()->values('Set-Cookie'));
    }

    public function testCsrfProtectionIsOptInForMutableMethodsAndSafeForPublicErrors(): void
    {
        $state = new SessionState(
            new SessionId(str_repeat('a', 43)),
            [],
            new DateTimeImmutable('2026-08-03T22:00:00+00:00'),
            new DateTimeImmutable('2026-08-03T22:00:00+00:00'),
            new: true,
        );
        $manager = new CsrfTokenManager();
        $token = $manager->token($state)->value();
        $middleware = new CsrfMiddleware($manager);
        $handler = new SessionProductPlainHandler();

        self::assertSame(200, $middleware->process($this->request(method: HttpMethod::Get), $handler)->status()->code());

        $valid = $this->request(
            method: HttpMethod::Post,
            headers: new HeaderBag(['X-CSRF-Token' => $token]),
        )->withAttribute(SessionRequestAttributes::STATE, $state);
        self::assertSame(200, $middleware->process($valid, $handler)->status()->code());

        $forbidden = $middleware->process($this->request(method: HttpMethod::Delete), $handler);
        self::assertSame(403, $forbidden->status()->code());
        self::assertSame('application/problem+json', $forbidden->body()->mediaType());
        self::assertStringNotContainsString($token, $forbidden->body()->contents());
        self::assertStringNotContainsString($state->id()->value(), $forbidden->body()->contents());
    }

    public function testDestroyedSessionDeletesStateAndExpiresCookie(): void
    {
        $store = new InMemorySessionStore();
        $middleware = new SessionMiddleware(new SessionRuntime(
            $store,
            new ProductCompletionSessionIdGenerator(),
            new ProductCompletionClock(),
        ));
        $created = $middleware->process($this->request(), new SessionProductCallbackHandler(
            static fn (SessionState $state): ResponseInterface => Response::text('created'),
        ));
        $identifier = $this->identifier($created);

        $destroyed = $middleware->process($this->request($identifier), new SessionProductCallbackHandler(
            static function (SessionState $state): ResponseInterface {
                $state->destroy();
                return Response::text('destroyed');
            },
        ));

        self::assertSame(0, $store->count());
        self::assertStringContainsString('Max-Age=0', $destroyed->headers()->values('Set-Cookie')[0] ?? '');
    }

    private function request(
        ?string $identifier = null,
        HttpMethod $method = HttpMethod::Get,
        HeaderBag $headers = new HeaderBag(),
    ): Request {
        $cookies = $identifier === null
            ? new CookieBag()
            : new CookieBag(['__Host-sif_session' => $identifier]);

        return new Request(
            $method,
            Uri::fromString('https://example.test/form'),
            headers: $headers,
            cookies: $cookies,
        );
    }

    private function identifier(ResponseInterface $response): string
    {
        $cookie = $response->headers()->values('Set-Cookie')[0] ?? '';
        return explode(';', substr($cookie, strlen('__Host-sif_session=')), 2)[0];
    }
}

final class SessionProductPlainHandler implements RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        return Response::json(['ok' => true]);
    }
}

final readonly class SessionProductCallbackHandler implements RequestHandlerInterface
{
    /** @param \Closure(SessionState): ResponseInterface $callback */
    public function __construct(private \Closure $callback)
    {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        $state = $request->attributes()->get(SessionRequestAttributes::STATE);
        if (!$state instanceof SessionState) {
            throw new \RuntimeException('Session state was not attached.');
        }

        return ($this->callback)($state);
    }
}

final class ProductCompletionSessionIdGenerator implements SessionIdGeneratorInterface
{
    private int $sequence = 0;

    public function generate(): SessionId
    {
        ++$this->sequence;
        return new SessionId(str_pad((string) $this->sequence, 32, 'p'));
    }
}

final class ProductCompletionClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2030-01-01T00:00:00+00:00');
    }
}
