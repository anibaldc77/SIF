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
use Sif\Foundation\Contracts\RuntimeInterface;

final readonly class RuntimeAboutCommand implements CliCommandInterface
{
    /** @param list<string> $capabilities */
    public function __construct(
        private RuntimeInterface $runtime,
        private array $capabilities = [],
    ) {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('runtime:about'),
            'Displays safe runtime identity and state information.',
            'Reports runtime state, boot stage and capability count without exposing environment secrets.',
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
            'SIF runtime information',
            [
                'state' => $this->runtime->state()->value,
                'stage' => $this->runtime->stage()->value,
                'failed' => $this->runtime->hasFailed(),
                'capability_count' => count($capabilities),
            ],
        );
    }
}
