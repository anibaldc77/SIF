<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Http\Exceptions\RouteGroupException;
use Sif\Foundation\Http\Routing\Advanced\RouteDefaults;
use Sif\Foundation\Http\Routing\Advanced\RouteGroupDefinition;
use Sif\Foundation\Http\Routing\Advanced\RouteGroupExpander;
use Sif\Foundation\Http\Routing\Advanced\RouteMetadata;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;
use Sif\Foundation\Http\Routing\RouteParameter;
use Sif\Foundation\Http\Value\HttpMethod;

final class AdvancedRouteGroupTest extends TestCase
{
    public function testNestedGroupsExpandPrefixesMiddlewareMetadataAndDefaults(): void
    {
        $route = new RouteDefinition(
            new RouteName('show'),
            [HttpMethod::Get],
            '/users/{id}',
            'handler.users-show',
            [new RouteParameter('id', '\\d+')],
            ['audit'],
        );

        $child = new RouteGroupDefinition(
            '/v1',
            'v1.',
            ['tenant'],
            new RouteMetadata(['api.version' => 1]),
            new RouteDefaults(['locale' => 'es']),
            [$route],
        );

        $root = new RouteGroupDefinition(
            '/api',
            'api.',
            ['request-id', 'tenant'],
            new RouteMetadata(['surface' => 'api']),
            new RouteDefaults(['format' => 'json']),
            [],
            [$child],
        );

        $expanded = (new RouteGroupExpander())->expand($root);

        self::assertCount(1, $expanded);
        self::assertSame('api.v1.show', $expanded[0]->route()->name()->value());
        self::assertSame('/api/v1/users/{id}', $expanded[0]->route()->path());
        self::assertSame(['request-id', 'tenant', 'audit'], $expanded[0]->route()->middleware());
        self::assertSame(['api.version' => 1, 'surface' => 'api'], $expanded[0]->metadata()->all());
        self::assertSame(['format' => 'json', 'locale' => 'es'], $expanded[0]->defaults()->all());
    }

    public function testExpansionPreservesDeclarationOrder(): void
    {
        $first = $this->route('first', '/first');
        $second = $this->route('second', '/second');

        $group = new RouteGroupDefinition(routes: [$first, $second]);
        $expanded = (new RouteGroupExpander())->expand($group);

        self::assertSame(['first', 'second'], array_map(
            static fn ($entry): string => $entry->route()->name()->value(),
            $expanded,
        ));
    }

    public function testConflictingMetadataFailsClosed(): void
    {
        $child = new RouteGroupDefinition(metadata: new RouteMetadata(['surface' => 'admin']));
        $root = new RouteGroupDefinition(
            metadata: new RouteMetadata(['surface' => 'api']),
            groups: [$child],
        );

        $this->expectException(RouteGroupException::class);
        (new RouteGroupExpander())->expand($root);
    }

    public function testInvalidPrefixesAreRejected(): void
    {
        $this->expectException(RouteGroupException::class);
        new RouteGroupDefinition('/api/', 'api.');
    }

    private function route(string $name, string $path): RouteDefinition
    {
        return new RouteDefinition(
            new RouteName($name),
            [HttpMethod::Get],
            $path,
            'handler.' . $name,
        );
    }
}
