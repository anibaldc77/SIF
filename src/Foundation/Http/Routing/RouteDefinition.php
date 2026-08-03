<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing;

use Sif\Foundation\Http\Exceptions\InvalidRouteDefinitionException;
use Sif\Foundation\Http\Value\HttpMethod;

final readonly class RouteDefinition
{
    /** @var list<HttpMethod> */
    private array $methods;

    /** @var array<string, RouteParameter> */
    private array $parameters;

    /** @var list<string> */
    private array $middleware;

    /**
     * @param list<HttpMethod> $methods
     * @param list<RouteParameter> $parameters
     * @param list<string> $middleware
     */
    public function __construct(
        private RouteName $name,
        array $methods,
        private string $path,
        private string $handler,
        array $parameters = [],
        array $middleware = [],
    ) {
        if ($methods === []) {
            throw new InvalidRouteDefinitionException('A route must support at least one HTTP method.');
        }

        $normalizedMethods = [];
        foreach ($methods as $method) {
            $normalizedMethods[$method->value] = $method;
        }
        ksort($normalizedMethods);
        $this->methods = array_values($normalizedMethods);

        if ($path === '' || $path[0] !== '/' || str_contains($path, '?') || str_contains($path, '#') || str_contains($path, '//')) {
            throw new InvalidRouteDefinitionException(sprintf('Invalid route path "%s".', $path));
        }

        if ($path !== '/' && str_ends_with($path, '/')) {
            throw new InvalidRouteDefinitionException('Route paths other than root must not end with a slash.');
        }

        if ($handler === '' || trim($handler) !== $handler || preg_match('/[\x00-\x1F\x7F]/', $handler) === 1) {
            throw new InvalidRouteDefinitionException('Route handler identifiers must be non-empty and printable.');
        }

        $normalizedParameters = [];
        foreach ($parameters as $parameter) {
            if (isset($normalizedParameters[$parameter->name()])) {
                throw new InvalidRouteDefinitionException(sprintf('Duplicate route parameter "%s".', $parameter->name()));
            }
            $normalizedParameters[$parameter->name()] = $parameter;
        }

        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $path, $matches);
        /** @var list<string> $placeholderNames */
        $placeholderNames = $matches[1];
        if (count($placeholderNames) !== count(array_unique($placeholderNames))) {
            throw new InvalidRouteDefinitionException('Route path contains duplicate parameter placeholders.');
        }
        sort($placeholderNames);
        $declaredNames = array_keys($normalizedParameters);
        sort($declaredNames);
        if ($placeholderNames !== $declaredNames) {
            throw new InvalidRouteDefinitionException('Declared route parameters must exactly match path placeholders.');
        }

        $normalizedMiddleware = [];
        foreach ($middleware as $identifier) {
            if ($identifier === '' || trim($identifier) !== $identifier) {
                throw new InvalidRouteDefinitionException('Middleware identifiers must be non-empty and trimmed.');
            }
            if (isset($normalizedMiddleware[$identifier])) {
                throw new InvalidRouteDefinitionException(sprintf('Duplicate middleware identifier "%s".', $identifier));
            }
            $normalizedMiddleware[$identifier] = $identifier;
        }

        $this->parameters = $normalizedParameters;
        $this->middleware = array_values($normalizedMiddleware);
    }

    public function name(): RouteName { return $this->name; }
    /** @return list<HttpMethod> */ public function methods(): array { return $this->methods; }
    public function path(): string { return $this->path; }
    public function handler(): string { return $this->handler; }
    /** @return array<string, RouteParameter> */ public function parameters(): array { return $this->parameters; }
    /** @return list<string> */ public function middleware(): array { return $this->middleware; }

    public function supports(HttpMethod $method): bool
    {
        foreach ($this->methods as $supported) {
            if ($supported === $method) {
                return true;
            }
        }
        return false;
    }

    public function signature(): string
    {
        return implode('|', array_map(static fn (HttpMethod $method): string => $method->value, $this->methods)) . ' ' . $this->path;
    }
}
