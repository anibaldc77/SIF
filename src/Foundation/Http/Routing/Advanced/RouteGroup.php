<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Routing\RouteDefinition;

final readonly class RouteGroup
{
    public function __construct(private RouteGroupDefinition $definition = new RouteGroupDefinition())
    {
    }

    public function definition(): RouteGroupDefinition
    {
        return $this->definition;
    }

    public function withRoute(RouteDefinition $route): self
    {
        return new self(new RouteGroupDefinition(
            $this->definition->pathPrefix(),
            $this->definition->namePrefix(),
            $this->definition->middleware(),
            $this->definition->metadata(),
            $this->definition->defaults(),
            [...$this->definition->routes(), $route],
            $this->definition->groups(),
        ));
    }

    public function withGroup(self|RouteGroupDefinition $group): self
    {
        $definition = $group instanceof self ? $group->definition() : $group;

        return new self(new RouteGroupDefinition(
            $this->definition->pathPrefix(),
            $this->definition->namePrefix(),
            $this->definition->middleware(),
            $this->definition->metadata(),
            $this->definition->defaults(),
            $this->definition->routes(),
            [...$this->definition->groups(), $definition],
        ));
    }
}
