<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced\Compilation;

use Sif\Foundation\Http\Routing\Advanced\DeterministicRouteMatcher;
use Sif\Foundation\Http\Routing\RouteMatch;
use Sif\Foundation\Http\Value\HttpMethod;

final readonly class CompiledRouteMatcher
{
    private DeterministicRouteMatcher $matcher;

    public function __construct(private CompiledRouteTable $table)
    {
        $this->matcher = new DeterministicRouteMatcher($table->routes());
    }

    public function table(): CompiledRouteTable { return $this->table; }

    public function match(HttpMethod $method, string $path): RouteMatch
    {
        return $this->matcher->match($method, $path);
    }
}
