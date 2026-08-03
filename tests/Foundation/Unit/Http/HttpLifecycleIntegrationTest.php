<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use DateTimeImmutable;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Contracts\ExecutionContextFactoryInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\EventDispatcherInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\Orchestration\ErrorHandlingResult;
use Sif\Foundation\Http\Dispatch\HandlerDispatcher;
use Sif\Foundation\Http\Dispatch\HandlerRegistry;
use Sif\Foundation\Http\Event\HttpRequestCompleted;
use Sif\Foundation\Http\Event\HttpRequestStarted;
use Sif\Foundation\Http\Lifecycle\HttpRequestLifecycleCoordinator;
use Sif\Foundation\Http\Middleware\MiddlewareRegistry;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteMatcher;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteRegistry;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\Uri;

final class HttpLifecycleIntegrationTest extends TestCase
{
    public function testLifecycleCreatesContextDispatchesEventsAndReturnsResponse(): void
    {
        $routes = new RouteRegistry();
        $routes->register(new RouteDefinition(new RouteName('health'), [HttpMethod::Get], '/health', 'health'));

        $handlers = new HandlerRegistry();
        $handlers->register('health', new LifecycleHandler());
        $middleware = new MiddlewareRegistry();
        $events = new LifecycleEvents();

        $coordinator = new HttpRequestLifecycleCoordinator(
            new RouteMatcher($routes),
            new HandlerDispatcher($handlers, $middleware),
            new LifecycleContextFactory(),
            new UnusedErrorHandler(),
            events: $events,
        );

        $response = $coordinator->handle(new Request(HttpMethod::Get, new Uri(path: '/health')));

        self::assertSame(200, $response->status()->code());
        self::assertSame([HttpRequestStarted::class, HttpRequestCompleted::class], $events->types);
    }

    public function testLifecycleTranslatesRoutingFailures(): void
    {
        $routes = new RouteRegistry();
        $routes->register(new RouteDefinition(new RouteName('health'), [HttpMethod::Get], '/health', 'health'));
        $handlers = new HandlerRegistry();
        $handlers->register('health', new LifecycleHandler());

        $coordinator = new HttpRequestLifecycleCoordinator(
            new RouteMatcher($routes),
            new HandlerDispatcher($handlers, new MiddlewareRegistry()),
            new LifecycleContextFactory(),
            new UnusedErrorHandler(),
        );

        self::assertSame(404, $coordinator->handle(new Request(HttpMethod::Get, new Uri(path: '/missing')))->status()->code());
        self::assertSame(405, $coordinator->handle(new Request(HttpMethod::Post, new Uri(path: '/health')))->status()->code());
    }
}

final class LifecycleHandler implements RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        if (!$request->attributes()->get('execution.context') instanceof \Sif\Foundation\Contracts\ExecutionContextInterface) {
            throw new RuntimeException('Missing execution context.');
        }
        return Response::text('ok');
    }
}

final class LifecycleEvents implements EventDispatcherInterface
{
    /** @var list<class-string> */
    public array $types = [];

    public function dispatch(object $event): object
    {
        $this->types[] = $event::class;
        return $event;
    }
}


final class LifecycleContextFactory implements ExecutionContextFactoryInterface
{
    private int $sequence = 0;

    public function createRoot(
        ContextAttributes $attributes = new ContextAttributes(),
        ?string $actorId = null,
        ?string $tenantId = null,
        ?string $operation = null,
        ?string $source = null,
        ?string $locale = null,
        ?string $timezone = null,
    ): ExecutionContext {
        $id = new ContextId('http-' . (++$this->sequence));
        return new ExecutionContext(
            contextId: $id,
            correlationId: $id,
            createdAt: new DateTimeImmutable('2026-08-02T00:00:00+00:00'),
            attributes: $attributes,
            actorId: $actorId,
            tenantId: $tenantId,
            operation: $operation,
            source: $source,
            locale: $locale,
            timezone: $timezone,
        );
    }

    public function derive(
        ExecutionContextInterface $parent,
        ContextAttributes $attributes = new ContextAttributes(),
        ?ContextId $causationId = null,
        ?string $operation = null,
        ?string $source = null,
    ): ExecutionContext {
        return $this->createRoot($parent->attributes()->merged($attributes), operation: $operation, source: $source);
    }
}

final class UnusedErrorHandler implements ErrorHandlerInterface
{
    public function handle(\Throwable $throwable, FailureOrigin $origin, array $metadata = [], int $attempt = 1): ErrorHandlingResult
    {
        throw new RuntimeException('Unexpected error handler invocation.');
    }
}
