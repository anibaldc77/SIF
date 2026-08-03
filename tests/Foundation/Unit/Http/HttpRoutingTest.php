<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Exceptions\DuplicateRouteException;
use Sif\Foundation\Http\Exceptions\InvalidRouteDefinitionException;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteMatchStatus;
use Sif\Foundation\Http\Routing\RouteMatcher;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Routing\RouteRegistry;
use Sif\Foundation\Http\Value\HttpMethod;

final class HttpRoutingTest extends TestCase
{
    public function testRegistryOrdersRoutesAndResolvesByName(): void
    {
        $registry = new RouteRegistry();
        $registry->register($this->route('users.show', '/users/{id}', [HttpMethod::Get], [new RouteParameter('id', '\\d+')]));
        $registry->register($this->route('health.show', '/health', [HttpMethod::Get]));

        self::assertSame(2, $registry->count());
        self::assertSame(['health.show', 'users.show'], array_map(
            static fn (RouteDefinition $route): string => $route->name()->value(),
            $registry->all(),
        ));
        self::assertSame('/users/{id}', $registry->get('users.show')->path());
    }

    public function testMatcherReturnsRouteAndDecodedParameters(): void
    {
        $registry = new RouteRegistry();
        $registry->register($this->route(
            'documents.show',
            '/documents/{slug}',
            [HttpMethod::Get, HttpMethod::Head],
            [new RouteParameter('slug', '[A-Za-z0-9%._-]+')],
            ['request-id', 'audit'],
        ));

        $match = (new RouteMatcher($registry))->match(HttpMethod::Get, '/documents/report%202026?download=1');

        self::assertTrue($match->isMatched());
        self::assertSame(RouteMatchStatus::Matched, $match->status());
        $route = $match->route();
        self::assertNotNull($route);
        self::assertSame('documents.show', $route->name()->value());
        self::assertSame(['slug' => 'report 2026'], $match->parameters());
        self::assertSame(['request-id', 'audit'], $route->middleware());
    }

    public function testMatcherDistinguishesMethodNotAllowedFromNotFound(): void
    {
        $registry = new RouteRegistry();
        $registry->register($this->route('users.store', '/users', [HttpMethod::Post]));
        $matcher = new RouteMatcher($registry);

        $methodNotAllowed = $matcher->match(HttpMethod::Get, '/users');
        self::assertSame(RouteMatchStatus::MethodNotAllowed, $methodNotAllowed->status());
        self::assertSame(['POST'], array_map(
            static fn (HttpMethod $method): string => $method->value,
            $methodNotAllowed->allowedMethods(),
        ));

        self::assertSame(RouteMatchStatus::NotFound, $matcher->match(HttpMethod::Get, '/missing')->status());
    }

    public function testRegistryRejectsDuplicateNameAndSignature(): void
    {
        $registry = new RouteRegistry();
        $registry->register($this->route('health.show', '/health', [HttpMethod::Get]));

        $this->expectException(DuplicateRouteException::class);
        $registry->register($this->route('health.index', '/health', [HttpMethod::Get]));
    }

    public function testDefinitionRequiresParametersToMatchPlaceholders(): void
    {
        $this->expectException(InvalidRouteDefinitionException::class);
        $this->route('users.show', '/users/{id}', [HttpMethod::Get]);
    }

    /**
     * @param list<HttpMethod> $methods
     * @param list<RouteParameter> $parameters
     * @param list<string> $middleware
     */
    private function route(
        string $name,
        string $path,
        array $methods,
        array $parameters = [],
        array $middleware = [],
    ): RouteDefinition {
        return new RouteDefinition(
            new RouteName($name),
            $methods,
            $path,
            'handler.' . str_replace('.', '-', $name),
            $parameters,
            $middleware,
        );
    }
}
