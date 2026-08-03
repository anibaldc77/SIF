<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing;

use Sif\Foundation\Http\Exceptions\InvalidRouteDefinitionException;

final readonly class RouteName
{
    public function __construct(private string $value)
    {
        if (preg_match('/^[a-z][a-z0-9]*(?:[.:-][a-z0-9]+)*$/', $value) !== 1) {
            throw new InvalidRouteDefinitionException(sprintf('Invalid route name "%s".', $value));
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
