<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Routing\Advanced\ConstrainedRouteDefinition;
use Sif\Foundation\Http\Routing\Advanced\ConstrainedRouteMatcher;
use Sif\Foundation\Http\Routing\Advanced\RouteHostConstraint;
use Sif\Foundation\Http\Routing\Advanced\RoutePortConstraint;
use Sif\Foundation\Http\Routing\Advanced\RouteRequestTarget;
use Sif\Foundation\Http\Routing\Advanced\RouteSchemeConstraint;
use Sif\Foundation\Http\Routing\Advanced\RouteTransportConstraints;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteMatchStatus;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Value\HttpMethod;

final class AdvancedRouteTransportConstraintTest extends TestCase
{
    public function testMatchesHostSchemePortAndMergesHostAndPathParameters(): void
    {
        $route = new RouteDefinition(new RouteName('tenant.users.show'), [HttpMethod::Get], '/users/{id}', 'users.show', [new RouteParameter('id', '\\d+')]);
        $matcher = new ConstrainedRouteMatcher([
            new ConstrainedRouteDefinition($route, new RouteTransportConstraints(
                new RouteHostConstraint('{tenant}.example.test'),
                new RouteSchemeConstraint('https'),
                new RoutePortConstraint(443),
            )),
        ]);

        $match = $matcher->match(new RouteRequestTarget(HttpMethod::Get, '/users/25', 'https', 'Acme.Example.Test', null));

        self::assertSame(RouteMatchStatus::Matched, $match->status());
        self::assertSame(['tenant' => 'acme', 'id' => '25'], $match->parameters());
    }

    public function testTransportMismatchIsNotFoundAndMethodMismatchIsMethodNotAllowed(): void
    {
        $route = new RouteDefinition(new RouteName('secure'), [HttpMethod::Get], '/secure', 'secure');
        $matcher = new ConstrainedRouteMatcher([
            new ConstrainedRouteDefinition($route, new RouteTransportConstraints(null, new RouteSchemeConstraint('https'))),
        ]);

        self::assertSame(RouteMatchStatus::NotFound, $matcher->match(new RouteRequestTarget(HttpMethod::Get, '/secure', 'http', 'example.test'))->status());
        self::assertSame(RouteMatchStatus::MethodNotAllowed, $matcher->match(new RouteRequestTarget(HttpMethod::Post, '/secure', 'https', 'example.test'))->status());
    }

    public function testUnconstrainedRouteRemainsCompatible(): void
    {
        $route = new RouteDefinition(new RouteName('health'), [HttpMethod::Get], '/health', 'health');
        $matcher = new ConstrainedRouteMatcher([new ConstrainedRouteDefinition($route)]);

        self::assertTrue($matcher->match(new RouteRequestTarget(HttpMethod::Get, '/health'))->isMatched());
    }
}
