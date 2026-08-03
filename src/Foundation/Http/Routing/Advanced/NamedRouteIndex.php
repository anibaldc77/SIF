<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteGroupException;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;

final readonly class NamedRouteIndex
{
    /** @var array<string, array{route: RouteDefinition, defaults: RouteDefaults}> */
    private array $entries;

    /** @param list<RouteDefinition|ExpandedRouteDefinition> $routes */
    public function __construct(array $routes)
    {
        $entries = [];
        foreach ($routes as $candidate) {
            $route = $candidate instanceof ExpandedRouteDefinition ? $candidate->route() : $candidate;
            $defaults = $candidate instanceof ExpandedRouteDefinition ? $candidate->defaults() : new RouteDefaults();
            $name = $route->name()->value();
            if (isset($entries[$name])) {
                throw new RouteGroupException(sprintf('Named route "%s" is duplicated.', $name));
            }
            $entries[$name] = ['route' => $route, 'defaults' => $defaults];
        }
        ksort($entries);
        $this->entries = $entries;
    }

    public function has(string|RouteName $name): bool
    {
        $value = $name instanceof RouteName ? $name->value() : $name;
        return isset($this->entries[$value]);
    }

    /** @return array{route: RouteDefinition, defaults: RouteDefaults}|null */
    public function find(string|RouteName $name): ?array
    {
        $value = $name instanceof RouteName ? $name->value() : $name;
        return $this->entries[$value] ?? null;
    }
}
