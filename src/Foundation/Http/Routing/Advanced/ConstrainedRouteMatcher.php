<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteMatch;

final readonly class ConstrainedRouteMatcher
{
    /** @var list<ConstrainedRouteDefinition> */
    private array $routes;

    /** @param list<ConstrainedRouteDefinition> $routes */
    public function __construct(array $routes)
    {
        $this->routes = array_values($routes);
    }

    public function match(RouteRequestTarget $target): RouteMatch
    {
        $allowed = [];
        foreach ($this->routes as $entry) {
            $transportParameters = $entry->constraints()->match($target);
            if ($transportParameters === null) {
                continue;
            }
            $pathParameters = $this->matchPath($entry->route(), $this->normalizePath($target->path()));
            if ($pathParameters === null) {
                continue;
            }
            if ($entry->route()->supports($target->method())) {
                return RouteMatch::matched($entry->route(), $transportParameters + $pathParameters);
            }
            foreach ($entry->route()->methods() as $method) {
                $allowed[] = $method;
            }
        }
        return $allowed === [] ? RouteMatch::notFound() : RouteMatch::methodNotAllowed($allowed);
    }

    private function normalizePath(string $path): string
    {
        $parsed = parse_url($path, PHP_URL_PATH);
        if (!is_string($parsed) || $parsed === '' || $parsed[0] !== '/') {
            return '/';
        }
        return $parsed !== '/' ? rtrim($parsed, '/') : '/';
    }

    /** @return array<string, string>|null */
    private function matchPath(RouteDefinition $route, string $path): ?array
    {
        if ($route->parameters() === []) {
            return $route->path() === $path ? [] : null;
        }
        $pattern = preg_quote($route->path(), '~');
        foreach ($route->parameters() as $parameter) {
            $pattern = str_replace(preg_quote('{' . $parameter->name() . '}', '~'), '(?P<' . $parameter->name() . '>' . $parameter->pattern() . ')', $pattern);
        }
        $matches = [];
        if (preg_match('~^' . $pattern . '$~uD', $path, $matches) !== 1) {
            return null;
        }
        $parameters = [];
        foreach ($route->parameters() as $parameter) {
            $value = $matches[$parameter->name()] ?? null;
            if (!is_string($value)) {
                return null;
            }
            $parameters[$parameter->name()] = rawurldecode($value);
        }
        return $parameters;
    }
}
