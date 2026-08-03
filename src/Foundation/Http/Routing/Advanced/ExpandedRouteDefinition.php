<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Routing\RouteDefinition;

final readonly class ExpandedRouteDefinition
{
    public function __construct(
        private RouteDefinition $route,
        private RouteMetadata $metadata,
        private RouteDefaults $defaults,
    ) {
    }

    public function route(): RouteDefinition { return $this->route; }
    public function metadata(): RouteMetadata { return $this->metadata; }
    public function defaults(): RouteDefaults { return $this->defaults; }
}
