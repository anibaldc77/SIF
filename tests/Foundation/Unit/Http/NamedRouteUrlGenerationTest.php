<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Routing\Advanced\ExpandedRouteDefinition;
use Sif\Foundation\Http\Routing\Advanced\NamedRouteIndex;
use Sif\Foundation\Http\Routing\Advanced\RouteBaseUri;
use Sif\Foundation\Http\Routing\Advanced\RouteDefaults;
use Sif\Foundation\Http\Routing\Advanced\RouteMetadata;
use Sif\Foundation\Http\Routing\Advanced\RouteUrlGenerator;
use Sif\Foundation\Http\Routing\Advanced\RouteUrlParameters;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Value\HttpMethod;

final class NamedRouteUrlGenerationTest extends TestCase
{
    public function testGeneratesRelativeAndAbsoluteUrls(): void
    {
        $route = new RouteDefinition(new RouteName('users.show'), [HttpMethod::Get], '/users/{id}', 'users.show', [new RouteParameter('id', '\\d+')]);
        $generator = new RouteUrlGenerator(new NamedRouteIndex([$route]));

        $relative = $generator->generate('users.show', new RouteUrlParameters(['id' => 25], ['page' => 2], 'details'));
        self::assertSame('/users/25?page=2#details', $relative->url());

        $absolute = $generator->generate('users.show', new RouteUrlParameters(['id' => 25]), RouteBaseUri::fromString('https://example.test/api'));
        self::assertSame('https://example.test/api/users/25', $absolute->url());
    }

    public function testUsesDefaultsAndReportsMissingOrInvalidParameters(): void
    {
        $route = new RouteDefinition(new RouteName('articles.page'), [HttpMethod::Get], '/articles/{page}', 'articles.page', [new RouteParameter('page', '\\d+')]);
        $expanded = new ExpandedRouteDefinition($route, new RouteMetadata(), new RouteDefaults(['page' => 1]));
        $generator = new RouteUrlGenerator(new NamedRouteIndex([$expanded]));

        self::assertSame('/articles/1', $generator->generate('articles.page')->url());
        self::assertFalse($generator->generate('articles.page', new RouteUrlParameters(['page' => 'bad']))->successful());
        self::assertFalse($generator->generate('missing')->successful());
    }
}
