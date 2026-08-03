<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Exceptions\RoutePrecedenceException;
use Sif\Foundation\Http\Routing\Advanced\DeterministicRouteMatcher;
use Sif\Foundation\Http\Routing\Advanced\OptionalRouteDefinition;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Value\HttpMethod;

final class OptionalRoutePrecedenceTest extends TestCase
{
    public function testTrailingOptionalParameterMatchesBothVariants(): void
    {
        $matcher = new DeterministicRouteMatcher([
            new OptionalRouteDefinition(new RouteName('articles.index'), [HttpMethod::Get], '/articles/{page?}', 'articles.index', [new RouteParameter('page', '\\d+')]),
        ]);

        self::assertTrue($matcher->match(HttpMethod::Get, '/articles')->isMatched());
        self::assertSame(['page' => '2'], $matcher->match(HttpMethod::Get, '/articles/2')->parameters());
    }

    public function testStaticRoutePrecedesParameterizedRoute(): void
    {
        $matcher = new DeterministicRouteMatcher([
            new RouteDefinition(new RouteName('users.show'), [HttpMethod::Get], '/users/{id}', 'users.show', [new RouteParameter('id')]),
            new RouteDefinition(new RouteName('users.new'), [HttpMethod::Get], '/users/new', 'users.new'),
        ]);

        self::assertSame('users.new', $matcher->match(HttpMethod::Get, '/users/new')->route()?->name()->value());
    }

    public function testConstrainedParameterPrecedesGenericParameter(): void
    {
        $matcher = new DeterministicRouteMatcher([
            new RouteDefinition(new RouteName('items.slug'), [HttpMethod::Get], '/items/{slug}', 'items.slug', [new RouteParameter('slug')]),
            new RouteDefinition(new RouteName('items.id'), [HttpMethod::Get], '/items/{id}', 'items.id', [new RouteParameter('id', '\\d+')]),
        ]);

        self::assertSame('items.id', $matcher->match(HttpMethod::Get, '/items/25')->route()?->name()->value());
    }

    public function testAmbiguousEffectiveSignaturesAreRejected(): void
    {
        $this->expectException(RoutePrecedenceException::class);
        new DeterministicRouteMatcher([
            new RouteDefinition(new RouteName('a'), [HttpMethod::Get], '/users/{id}', 'a', [new RouteParameter('id')]),
            new RouteDefinition(new RouteName('b'), [HttpMethod::Get], '/users/{slug}', 'b', [new RouteParameter('slug')]),
        ]);
    }
}
