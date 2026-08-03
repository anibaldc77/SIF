<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RoutePrecedenceException;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteMatch;
use Sif\Foundation\Http\Value\HttpMethod;

final readonly class DeterministicRouteMatcher
{
    /** @var list<RouteDefinition> */
    private array $routes;

    /** @param list<RouteDefinition|OptionalRouteDefinition> $routes */
    public function __construct(array $routes)
    {
        $expanded = [];
        $priorityByObject = [];
        foreach ($routes as $route) {
            if ($route instanceof OptionalRouteDefinition) {
                foreach ($route->expand() as $variant) {
                    $expanded[] = $variant;
                    $priorityByObject[spl_object_id($variant)] = $route->priority();
                }
                continue;
            }
            $expanded[] = $route;
            $priorityByObject[spl_object_id($route)] = 0;
        }

        $this->assertUnambiguous($expanded);
        $precedence = new RoutePrecedence();
        usort($expanded, static fn (RouteDefinition $a, RouteDefinition $b): int => $precedence->compare(
            $a,
            $b,
            $priorityByObject[spl_object_id($a)] ?? 0,
            $priorityByObject[spl_object_id($b)] ?? 0,
        ));
        $this->routes = $expanded;
    }

    public function match(HttpMethod $method, string $path): RouteMatch
    {
        $normalizedPath = $this->normalizePath($path);
        $allowed = [];
        foreach ($this->routes as $route) {
            $parameters = $this->matchPath($route, $normalizedPath);
            if ($parameters === null) {
                continue;
            }
            if ($route->supports($method)) {
                return RouteMatch::matched($route, $parameters);
            }
            foreach ($route->methods() as $allowedMethod) {
                $allowed[] = $allowedMethod;
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
            $pattern = str_replace(
                preg_quote('{' . $parameter->name() . '}', '~'),
                '(?P<' . $parameter->name() . '>' . $parameter->pattern() . ')',
                $pattern,
            );
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

    /** @param list<RouteDefinition> $routes */
    private function assertUnambiguous(array $routes): void
    {
        $seen = [];
        foreach ($routes as $route) {
            $shape = $route->path();
            foreach ($route->parameters() as $parameter) {
                $shape = str_replace('{' . $parameter->name() . '}', '{' . $parameter->pattern() . '}', $shape);
            }
            foreach ($route->methods() as $method) {
                $key = $method->value . ' ' . $shape;
                if (isset($seen[$key])) {
                    throw new RoutePrecedenceException(sprintf('Ambiguous effective route signature "%s".', $key));
                }
                $seen[$key] = true;
            }
        }
    }
}
