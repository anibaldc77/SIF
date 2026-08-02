<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Runtime;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;

final readonly class RuntimeCapabilitiesCommand implements CliCommandInterface
{
    /** @param list<string> $capabilities */
    public function __construct(private array $capabilities)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('runtime:capabilities'),
            'Lists runtime capabilities.',
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
        $capabilities = array_values(array_unique($this->capabilities));
        sort($capabilities);

        return new CliCommandResult(
            CliExitCode::success(),
            'Runtime capabilities',
            ['capabilities' => $capabilities, 'count' => count($capabilities)],
        );
    }
}
