<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteGroupException;
use Sif\Foundation\Http\Routing\RouteDefinition;

final readonly class RouteGroupDefinition
{
    /** @var list<string> */
    private array $middleware;

    /** @var list<RouteDefinition> */
    private array $routes;

    /** @var list<RouteGroupDefinition> */
    private array $groups;

    /**
     * @param list<string> $middleware
     * @param list<RouteDefinition> $routes
     * @param list<RouteGroupDefinition> $groups
     */
    public function __construct(
        private string $pathPrefix = '',
        private string $namePrefix = '',
        array $middleware = [],
        private RouteMetadata $metadata = new RouteMetadata(),
        private RouteDefaults $defaults = new RouteDefaults(),
        array $routes = [],
        array $groups = [],
    ) {
        if ($pathPrefix !== '' && ($pathPrefix[0] !== '/' || str_ends_with($pathPrefix, '/') || str_contains($pathPrefix, '//'))) {
            throw new RouteGroupException(sprintf('Invalid route-group path prefix "%s".', $pathPrefix));
        }
        if ($namePrefix !== '' && preg_match('/^[a-z][a-z0-9]*(?:[.:-][a-z0-9]+)*[.:-]$/', $namePrefix) !== 1) {
            throw new RouteGroupException(sprintf('Invalid route-group name prefix "%s".', $namePrefix));
        }

        $seen = [];
        $normalizedMiddleware = [];
        foreach ($middleware as $identifier) {
            if ($identifier === '' || trim($identifier) !== $identifier) {
                throw new RouteGroupException('Middleware identifiers must be non-empty and trimmed.');
            }
            if (isset($seen[$identifier])) {
                throw new RouteGroupException(sprintf('Duplicate middleware identifier "%s" in route group.', $identifier));
            }
            $seen[$identifier] = true;
            $normalizedMiddleware[] = $identifier;
        }

        $this->middleware = $normalizedMiddleware;
        $this->routes = array_values($routes);
        $this->groups = array_values($groups);
    }

    public function pathPrefix(): string { return $this->pathPrefix; }
    public function namePrefix(): string { return $this->namePrefix; }
    /** @return list<string> */ public function middleware(): array { return $this->middleware; }
    public function metadata(): RouteMetadata { return $this->metadata; }
    public function defaults(): RouteDefaults { return $this->defaults; }
    /** @return list<RouteDefinition> */ public function routes(): array { return $this->routes; }
    /** @return list<RouteGroupDefinition> */ public function groups(): array { return $this->groups; }
}
