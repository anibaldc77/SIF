<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Command\Routing\RouteCacheInspectCommand;
use Sif\Foundation\Cli\Command\Routing\RouteListCommand;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Http\Routing\Advanced\Compilation\CompiledRouteTable;

final readonly class AdvancedRoutingCommandContributor implements CliCommandContributorInterface
{
    public function __construct(private CompiledRouteTable $table) {}

    /** @return list<CliCommandInterface> */
    public function commands(): array
    {
        return [
            new RouteCacheInspectCommand($this->table),
            new RouteListCommand($this->table),
        ];
    }
}
