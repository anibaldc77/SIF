<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Maintenance;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;

final readonly class MaintenanceSummaryCommand implements CliCommandInterface
{
    /** @param array<string, scalar|null> $summary */
    public function __construct(private array $summary)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('maintenance:summary'),
            'Displays maintenance state without mutating the application.',
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
        $summary = $this->summary;
        ksort($summary);

        return new CliCommandResult(
            CliExitCode::success(),
            'Maintenance summary',
            ['summary' => $summary],
        );
    }
}
