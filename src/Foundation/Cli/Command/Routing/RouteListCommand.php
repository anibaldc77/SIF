<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Routing;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Http\Routing\Advanced\Compilation\CompiledRouteTable;
use Sif\Foundation\Http\Routing\RouteDefinition;

final readonly class RouteListCommand implements CliCommandInterface
{
    public function __construct(private CompiledRouteTable $table) {}

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('route:list'),
            'Lists compiled routes and safe routing metadata.',
            null,
            [],
            [],
            CliOperationalClass::inspection(),
            false,
            false,
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $routes = array_map(
            static fn (RouteDefinition $route): array => [
                'name' => $route->name()->value(),
                'methods' => array_map(static fn ($method): string => $method->value, $route->methods()),
                'path' => $route->path(),
                'handler' => $route->handler(),
                'middleware' => $route->middleware(),
            ],
            $this->table->routes(),
        );

        return new CliCommandResult(
            CliExitCode::success(),
            'Compiled routes',
            [
                'routes' => $routes,
                'count' => count($routes),
                'format_version' => $this->table->formatVersion(),
                'fingerprint' => $this->table->fingerprint(),
            ],
        );
    }
}
