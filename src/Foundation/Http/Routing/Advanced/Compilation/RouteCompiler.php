<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced\Compilation;

use Sif\Foundation\Http\Routing\Advanced\DeterministicRouteMatcher;
use Sif\Foundation\Http\Routing\Advanced\OptionalRouteDefinition;
use Sif\Foundation\Http\Routing\RouteDefinition;

final readonly class RouteCompiler
{
    /** @param list<RouteDefinition|OptionalRouteDefinition> $routes */
    public function compile(array $routes): RouteCompilationResult
    {
        try {
            $expanded = [];
            foreach ($routes as $route) {
                if ($route instanceof OptionalRouteDefinition) {
                    foreach ($route->expand() as $variant) {
                        $expanded[] = $variant;
                    }
                    continue;
                }
                $expanded[] = $route;
            }

            new DeterministicRouteMatcher($routes);
            $canonical = $this->canonicalize($expanded);
            $fingerprint = hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

            return new RouteCompilationResult(new CompiledRouteTable($expanded, $fingerprint));
        } catch (\Throwable $throwable) {
            return new RouteCompilationResult(null, [new RouteDiagnostic(
                'ROUTE-COMPILE-001',
                'Route compilation failed.',
                'error',
                ['exception' => $throwable::class],
            )]);
        }
    }

    /**
     * @param list<RouteDefinition> $routes
     * @return list<array<string, mixed>>
     */
    private function canonicalize(array $routes): array
    {
        $canonical = [];
        foreach ($routes as $route) {
            $parameters = [];
            foreach ($route->parameters() as $parameter) {
                $parameters[] = ['name' => $parameter->name(), 'pattern' => $parameter->pattern()];
            }
            $canonical[] = [
                'name' => $route->name()->value(),
                'methods' => array_map(static fn ($method): string => $method->value, $route->methods()),
                'path' => $route->path(),
                'handler' => $route->handler(),
                'parameters' => $parameters,
                'middleware' => $route->middleware(),
            ];
        }
        return $canonical;
    }
}
