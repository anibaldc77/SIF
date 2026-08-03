<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteGroupException;
use Sif\Foundation\Http\Routing\RouteDefinition;
use Sif\Foundation\Http\Routing\RouteName;

final class RouteGroupExpander
{
    /** @return list<ExpandedRouteDefinition> */
    public function expand(RouteGroup|RouteGroupDefinition $group): array
    {
        $definition = $group instanceof RouteGroup ? $group->definition() : $group;

        return $this->expandDefinition(
            $definition,
            '',
            '',
            [],
            new RouteMetadata(),
            new RouteDefaults(),
        );
    }

    /**
     * @param list<string> $parentMiddleware
     * @return list<ExpandedRouteDefinition>
     */
    private function expandDefinition(
        RouteGroupDefinition $group,
        string $parentPath,
        string $parentName,
        array $parentMiddleware,
        RouteMetadata $parentMetadata,
        RouteDefaults $parentDefaults,
    ): array {
        $pathPrefix = $this->joinPath($parentPath, $group->pathPrefix());
        $namePrefix = $parentName . $group->namePrefix();
        $middleware = $this->mergeMiddleware($parentMiddleware, $group->middleware());
        $metadata = $parentMetadata->merge($group->metadata());
        $defaults = $parentDefaults->merge($group->defaults());

        $expanded = [];
        foreach ($group->routes() as $route) {
            $expanded[] = new ExpandedRouteDefinition(
                $this->expandRoute($route, $pathPrefix, $namePrefix, $middleware),
                $metadata,
                $defaults,
            );
        }

        foreach ($group->groups() as $child) {
            array_push($expanded, ...$this->expandDefinition(
                $child,
                $pathPrefix,
                $namePrefix,
                $middleware,
                $metadata,
                $defaults,
            ));
        }

        return $expanded;
    }

    /** @param list<string> $groupMiddleware */
    private function expandRoute(RouteDefinition $route, string $pathPrefix, string $namePrefix, array $groupMiddleware): RouteDefinition
    {
        $path = $this->joinPath($pathPrefix, $route->path());
        $name = new RouteName($namePrefix . $route->name()->value());
        $middleware = $this->mergeMiddleware($groupMiddleware, $route->middleware());

        return new RouteDefinition(
            $name,
            $route->methods(),
            $path,
            $route->handler(),
            array_values($route->parameters()),
            $middleware,
        );
    }

    private function joinPath(string $prefix, string $path): string
    {
        if ($prefix === '' && $path === '') {
            return '/';
        }

        if ($prefix === '' || $prefix === '/') {
            return $path === '' || $path === '/' ? '/' : '/' . ltrim($path, '/');
        }

        if ($path === '' || $path === '/') {
            return '/' . trim($prefix, '/');
        }

        $joined = '/' . trim($prefix, '/') . '/' . ltrim($path, '/');
        if (str_contains($joined, '//')) {
            throw new RouteGroupException(sprintf('Route-group expansion produced invalid path "%s".', $joined));
        }

        return $joined;
    }

    /**
     * @param list<string> $parent
     * @param list<string> $child
     * @return list<string>
     */
    private function mergeMiddleware(array $parent, array $child): array
    {
        $merged = [];
        foreach ([...$parent, ...$child] as $identifier) {
            if (!in_array($identifier, $merged, true)) {
                $merged[] = $identifier;
            }
        }

        return $merged;
    }
}
