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

final readonly class RouteCacheInspectCommand implements CliCommandInterface
{
    public function __construct(private CompiledRouteTable $table) {}

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('route:cache:inspect'),
            'Inspects the compiled route-cache identity without exposing executable objects.',
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
        return new CliCommandResult(
            CliExitCode::success(),
            'Route cache',
            [
                'route_count' => count($this->table->routes()),
                'format_version' => $this->table->formatVersion(),
                'fingerprint' => $this->table->fingerprint(),
            ],
        );
    }
}
