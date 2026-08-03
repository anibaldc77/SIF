<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing;

use Sif\Foundation\Http\Exceptions\DuplicateRouteException;
use Sif\Foundation\Http\Exceptions\RouteNotFoundException;

final class RouteRegistry
{
    /** @var array<string, RouteDefinition> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $signatures = [];

    public function register(RouteDefinition $route): void
    {
        $name = $route->name()->value();
        if (isset($this->routes[$name])) {
            throw new DuplicateRouteException(sprintf('Route name "%s" is already registered.', $name));
        }

        $signature = $route->signature();
        if (isset($this->signatures[$signature])) {
            throw new DuplicateRouteException(sprintf('Route signature "%s" is already registered by "%s".', $signature, $this->signatures[$signature]));
        }

        $this->routes[$name] = $route;
        $this->signatures[$signature] = $name;
    }

    public function get(string|RouteName $name): RouteDefinition
    {
        $value = $name instanceof RouteName ? $name->value() : $name;

        return $this->routes[$value]
            ?? throw new RouteNotFoundException(sprintf('Route "%s" is not registered.', $value));
    }

    public function has(string|RouteName $name): bool
    {
        $value = $name instanceof RouteName ? $name->value() : $name;
        return isset($this->routes[$value]);
    }

    /** @return list<RouteDefinition> */
    public function all(): array
    {
        $routes = $this->routes;
        ksort($routes);
        return array_values($routes);
    }

    public function count(): int
    {
        return count($this->routes);
    }
}
