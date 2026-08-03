<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing;

use Sif\Foundation\Http\Value\HttpMethod;

final readonly class RouteMatcher
{
    public function __construct(private RouteRegistry $registry)
    {
    }

    public function match(HttpMethod $method, string $path): RouteMatch
    {
        $normalizedPath = $this->normalizePath($path);
        $allowedMethods = [];

        foreach ($this->registry->all() as $route) {
            $parameters = $this->matchPath($route, $normalizedPath);
            if ($parameters === null) {
                continue;
            }

            if ($route->supports($method)) {
                return RouteMatch::matched($route, $parameters);
            }

            foreach ($route->methods() as $allowedMethod) {
                $allowedMethods[] = $allowedMethod;
            }
        }

        return $allowedMethods === []
            ? RouteMatch::notFound()
            : RouteMatch::methodNotAllowed($allowedMethods);
    }

    private function normalizePath(string $path): string
    {
        if ($path === '') {
            return '/';
        }

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
            $placeholder = preg_quote('{' . $parameter->name() . '}', '~');
            $pattern = str_replace($placeholder, '(?P<' . $parameter->name() . '>' . $parameter->pattern() . ')', $pattern);
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
