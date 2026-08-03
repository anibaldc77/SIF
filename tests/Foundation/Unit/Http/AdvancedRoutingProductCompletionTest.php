<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Routing\Advanced\Compilation\CompiledRouteMatcher;
use Sif\Foundation\Http\Routing\Advanced\Compilation\RouteCacheSerializer;
use Sif\Foundation\Http\Routing\Advanced\Compilation\RouteCompiler;
use Sif\Foundation\Http\Routing\Advanced\NamedRouteIndex;
use Sif\Foundation\Http\Routing\Advanced\RouteUrlGenerator;
use Sif\Foundation\Http\Routing\Advanced\RouteUrlParameters;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteMatcher;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Routing\RouteRegistry;
use Sif\Foundation\Http\Value\HttpMethod;

final class AdvancedRoutingProductCompletionTest extends TestCase
{
    public function testBasicRouteMatcherRemainsCompatibleWithoutAdvancedComposition(): void
    {
        $route = new RouteDefinition(
            new RouteName('legacy.show'),
            [HttpMethod::Get],
            '/legacy/{id}',
            'legacy.show',
            [new RouteParameter('id', '\\d+')],
        );
        $registry = new RouteRegistry();
        $registry->register($route);

        $match = (new RouteMatcher($registry))->match(HttpMethod::Get, '/legacy/25');

        self::assertTrue($match->isMatched());
        self::assertSame(['id' => '25'], $match->parameters());
    }

    public function testNamedUrlGenerationRequiresExplicitIndexComposition(): void
    {
        $route = new RouteDefinition(
            new RouteName('users.show'),
            [HttpMethod::Get],
            '/users/{id}',
            'users.show',
            [new RouteParameter('id', '\\d+')],
        );
        $generator = new RouteUrlGenerator(new NamedRouteIndex([$route]));

        $result = $generator->generate('users.show', new RouteUrlParameters(values: ['id' => 7]));

        self::assertTrue($result->successful());
        self::assertSame('/users/7', $result->url());
    }

    public function testCompiledCacheRoundTripPreservesMatchingSemantics(): void
    {
        $route = new RouteDefinition(
            new RouteName('health'),
            [HttpMethod::Get],
            '/health',
            'health',
        );
        $compiled = (new RouteCompiler())->compile([$route]);
        $table = $compiled->table();
        self::assertNotNull($table);

        $serializer = new RouteCacheSerializer();
        $decoded = $serializer->decode($serializer->encode($table));
        $decodedTable = $decoded->table();
        self::assertNotNull($decodedTable);

        $match = (new CompiledRouteMatcher($decodedTable))->match(HttpMethod::Get, '/health');

        self::assertTrue($match->isMatched());
        self::assertSame('health', $match->route()?->name()->value());
    }

    public function testAdvancedFeaturesRemainOptInDataComponents(): void
    {
        $index = new NamedRouteIndex([]);
        $compiled = (new RouteCompiler())->compile([]);

        self::assertFalse($index->has('missing'));
        self::assertTrue($compiled->successful());
        self::assertSame(0, count($compiled->table()?->routes() ?? []));
    }
}
