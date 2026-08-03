<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\HttpMiddlewareInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Dispatch\HandlerDispatcher;
use Sif\Foundation\Http\Dispatch\HandlerRegistry;
use Sif\Foundation\Http\Exceptions\InvalidMiddlewarePipelineException;
use Sif\Foundation\Http\Middleware\MiddlewareRegistry;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteMatch;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;

final class HttpMiddlewareDispatchTest extends TestCase
{
    public function testDispatcherRunsGlobalThenRouteMiddlewareThenHandler(): void
    {
        $events = [];
        $handlers = new HandlerRegistry();
        $middleware = new MiddlewareRegistry();

        $handlers->register('handler.users-show', new CallbackHandler(function (RequestInterface $request) use (&$events): ResponseInterface {
            $events[] = 'handler';
            self::assertSame('users.show', $request->attributes()->get('route.name'));
            self::assertSame('42', $request->attributes()->get('route.parameter.id'));
            return Response::text('ok');
        }));

        $middleware->register('global', new CallbackMiddleware(function (RequestInterface $request, RequestHandlerInterface $next) use (&$events): ResponseInterface {
            $events[] = 'global.before';
            $response = $next->handle($request);
            $events[] = 'global.after';
            return $response;
        }));
        $middleware->register('route', new CallbackMiddleware(function (RequestInterface $request, RequestHandlerInterface $next) use (&$events): ResponseInterface {
            $events[] = 'route.before';
            $response = $next->handle($request);
            $events[] = 'route.after';
            return $response;
        }));

        $route = new RouteDefinition(new RouteName('users.show'), [HttpMethod::Get], '/users/{id}', 'handler.users-show', [new \Sif\Foundation\Http\Routing\RouteParameter('id', '\\d+')], ['route']);
        $dispatcher = new HandlerDispatcher($handlers, $middleware, ['global']);
        $response = $dispatcher->dispatch(new Request(HttpMethod::Get, Uri::fromString('https://example.test/users/42')), RouteMatch::matched($route, ['id' => '42']));

        self::assertSame(200, $response->status()->code());
        self::assertSame(['global.before', 'route.before', 'handler', 'route.after', 'global.after'], $events);
    }

    public function testMiddlewareMayShortCircuitThePipeline(): void
    {
        $handlers = new HandlerRegistry();
        $middleware = new MiddlewareRegistry();
        $handlers->register('handler.unreachable', new CallbackHandler(static fn (RequestInterface $request): ResponseInterface => Response::text('unreachable')));
        $middleware->register('maintenance', new CallbackMiddleware(static fn (RequestInterface $request, RequestHandlerInterface $next): ResponseInterface => Response::text('maintenance', 503)));
        $route = new RouteDefinition(new RouteName('maintenance'), [HttpMethod::Get], '/maintenance', 'handler.unreachable', middleware: ['maintenance']);

        $response = (new HandlerDispatcher($handlers, $middleware))->dispatch(new Request(HttpMethod::Get, Uri::fromString('/maintenance')), RouteMatch::matched($route, []));

        self::assertSame(503, $response->status()->code());
        self::assertSame('maintenance', $response->body()->contents());
    }

    public function testNextHandlerCannotBeInvokedTwice(): void
    {
        $handlers = new HandlerRegistry();
        $middleware = new MiddlewareRegistry();
        $handlers->register('handler.ok', new CallbackHandler(static fn (RequestInterface $request): ResponseInterface => Response::text('ok')));
        $middleware->register('invalid', new CallbackMiddleware(static function (RequestInterface $request, RequestHandlerInterface $next): ResponseInterface {
            $next->handle($request);
            return $next->handle($request);
        }));
        $route = new RouteDefinition(new RouteName('invalid'), [HttpMethod::Get], '/invalid', 'handler.ok', middleware: ['invalid']);

        $this->expectException(InvalidMiddlewarePipelineException::class);
        (new HandlerDispatcher($handlers, $middleware))->dispatch(new Request(HttpMethod::Get, Uri::fromString('/invalid')), RouteMatch::matched($route, []));
    }
}

final readonly class CallbackHandler implements RequestHandlerInterface
{
    /** @param \Closure(RequestInterface): ResponseInterface $callback */
    public function __construct(private \Closure $callback) {}
    public function handle(RequestInterface $request): ResponseInterface { return ($this->callback)($request); }
}

final readonly class CallbackMiddleware implements HttpMiddlewareInterface
{
    /** @param \Closure(RequestInterface, RequestHandlerInterface): ResponseInterface $callback */
    public function __construct(private \Closure $callback) {}
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface { return ($this->callback)($request, $next); }
}
