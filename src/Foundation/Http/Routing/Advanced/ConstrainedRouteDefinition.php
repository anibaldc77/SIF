<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Routing\RouteDefinition;

final readonly class ConstrainedRouteDefinition
{
    public function __construct(
        private RouteDefinition $route,
        private RouteTransportConstraints $constraints = new RouteTransportConstraints(),
    ) {
    }

    public function route(): RouteDefinition { return $this->route; }
    public function constraints(): RouteTransportConstraints { return $this->constraints; }
}
