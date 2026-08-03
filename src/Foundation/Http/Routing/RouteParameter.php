<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing;

use Sif\Foundation\Http\Exceptions\InvalidRouteDefinitionException;

final readonly class RouteParameter
{
    public function __construct(
        private string $name,
        private string $pattern = '[^/]+',
    ) {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new InvalidRouteDefinitionException(sprintf('Invalid route parameter name "%s".', $name));
        }

        if ($pattern === '' || str_contains($pattern, '~')) {
            throw new InvalidRouteDefinitionException('Route parameter patterns must be non-empty and cannot contain the internal delimiter.');
        }

        if (@preg_match('~^(?:' . $pattern . ')$~u', '') === false) {
            throw new InvalidRouteDefinitionException(sprintf('Invalid route parameter pattern for "%s".', $name));
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }
}
