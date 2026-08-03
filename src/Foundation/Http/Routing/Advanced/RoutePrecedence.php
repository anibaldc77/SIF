<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Routing\RouteDefinition;

final readonly class RoutePrecedence
{
    /** @return array{int, int, int, int, string} */
    public function key(RouteDefinition $route, int $priority = 0): array
    {
        $segments = array_values(array_filter(explode('/', trim($route->path(), '/')), static fn (string $segment): bool => $segment !== ''));
        $static = 0;
        $constrained = 0;
        foreach ($segments as $segment) {
            if (!str_starts_with($segment, '{')) {
                ++$static;
                continue;
            }
            $name = trim($segment, '{}');
            $parameter = $route->parameters()[$name] ?? null;
            if ($parameter !== null && $parameter->pattern() !== '[^/]+') {
                ++$constrained;
            }
        }

        return [$static, $constrained, count($segments), $priority, $route->path()];
    }

    public function compare(RouteDefinition $left, RouteDefinition $right, int $leftPriority = 0, int $rightPriority = 0): int
    {
        $a = $this->key($left, $leftPriority);
        $b = $this->key($right, $rightPriority);
        for ($i = 0; $i < 4; ++$i) {
            if ($a[$i] !== $b[$i]) {
                return $b[$i] <=> $a[$i];
            }
        }
        return $a[4] <=> $b[4];
    }
}
