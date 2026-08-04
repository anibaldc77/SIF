<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\CookieBag;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;
use Sif\Foundation\Session\Contracts\SessionIdGeneratorInterface;
use Sif\Foundation\Session\Middleware\SessionMiddleware;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionRequestAttributes;
use Sif\Foundation\Session\SessionRuntime;
use Sif\Foundation\Session\SessionState;
use Sif\Foundation\Session\Storage\InMemorySessionStore;

final class SessionLifecycleMiddlewareTest extends TestCase
{
    public function testCreatesPersistsAndReopensSessionThroughCookie(): void
    {
        $store = new InMemorySessionStore();
        $middleware = $this->middleware($store);
        $response = $middleware->process($this->request(), new SessionCallbackHandler(static function (SessionState $state): ResponseInterface {
            $state->put('count', 1);
            return Response::text('created');
        }));

        $cookie = $this->cookieValue($response);
        self::assertNotNull($cookie);
        $identifier = explode(';', substr($cookie, strlen('__Host-sif_session=')), 2)[0];

        $reopened = $middleware->process($this->request($identifier), new SessionCallbackHandler(static function (SessionState $state): ResponseInterface {
            self::assertSame(1, $state->get('count'));
            return Response::text('reopened');
        }));

        self::assertSame([], $reopened->headers()->values('Set-Cookie'));
    }

    public function testRegenerationAndDestructionProduceReplacementAndRemovalCookies(): void
    {
        $store = new InMemorySessionStore();
        $middleware = $this->middleware($store);
        $created = $middleware->process($this->request(), new SessionCallbackHandler(static fn (SessionState $state): ResponseInterface => Response::text('ok')));
        $first = $this->identifier($created);

        $regenerated = $middleware->process($this->request($first), new SessionCallbackHandler(static function (SessionState $state): ResponseInterface {
            $state->requestRegeneration();
            return Response::text('rotated');
        }));
        $second = $this->identifier($regenerated);
        self::assertNotSame($first, $second);

        $destroyed = $middleware->process($this->request($second), new SessionCallbackHandler(static function (SessionState $state): ResponseInterface {
            $state->destroy();
            return Response::text('destroyed');
        }));
        self::assertStringContainsString('Max-Age=0', (string) $this->cookieValue($destroyed));
    }

    public function testHandlerExceptionsPropagateWithoutCommit(): void
    {
        $store = new InMemorySessionStore();
        $middleware = $this->middleware($store);

        $this->expectException(RuntimeException::class);
        try {
            $middleware->process($this->request(), new SessionCallbackHandler(static function (SessionState $state): ResponseInterface {
                $state->put('partial', true);
                throw new RuntimeException('boom');
            }));
        } finally {
            self::assertSame(0, $store->count());
        }
    }

    private function middleware(InMemorySessionStore $store): SessionMiddleware
    {
        return new SessionMiddleware(new SessionRuntime($store, new MiddlewareSessionIdGenerator(), new MiddlewareSessionClock()));
    }

    private function request(?string $identifier = null): Request
    {
        $cookies = $identifier === null ? new CookieBag() : new CookieBag(['__Host-sif_session' => $identifier]);
        return new Request(HttpMethod::Get, Uri::fromString('https://example.test/'), cookies: $cookies);
    }

    private function cookieValue(ResponseInterface $response): ?string
    {
        return $response->headers()->values('Set-Cookie')[0] ?? null;
    }

    private function identifier(ResponseInterface $response): string
    {
        $cookie = (string) $this->cookieValue($response);
        return explode(';', substr($cookie, strlen('__Host-sif_session=')), 2)[0];
    }
}

final readonly class SessionCallbackHandler implements RequestHandlerInterface
{
    /** @param \Closure(SessionState): ResponseInterface $callback */
    public function __construct(private \Closure $callback) {}

    public function handle(RequestInterface $request): ResponseInterface
    {
        $state = $request->attributes()->get(SessionRequestAttributes::STATE);
        if (!$state instanceof SessionState) {
            throw new RuntimeException('Session state was not attached.');
        }
        return ($this->callback)($state);
    }
}

final class MiddlewareSessionIdGenerator implements SessionIdGeneratorInterface
{
    private int $sequence = 0;
    public function generate(): SessionId
    {
        ++$this->sequence;
        return new SessionId(str_pad((string) $this->sequence, 32, 's'));
    }
}

final class MiddlewareSessionClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2030-01-01T00:00:00+00:00');
    }
}
