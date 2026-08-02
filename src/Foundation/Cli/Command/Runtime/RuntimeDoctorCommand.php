<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Runtime;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Diagnostics\CliRuntimeDiagnostic;
use Sif\Foundation\Cli\Diagnostics\CliRuntimeDiagnosticReport;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Configuration\Contracts\ConfigurationInterface;
use Sif\Foundation\Contracts\RuntimeInterface;

final readonly class RuntimeDoctorCommand implements CliCommandInterface
{
    public function __construct(
        private RuntimeInterface $runtime,
        private ConfigurationInterface $configuration,
    ) {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('runtime:doctor'),
            'Runs safe runtime diagnostics.',
            'Checks runtime failure state and configuration availability without mutating application state.',
            [],
            [],
            CliOperationalClass::validation(),
            false,
            false,
            [new CliCommandName('runtime:diagnose')],
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $report = new CliRuntimeDiagnosticReport([
            new CliRuntimeDiagnostic(
                'RUNTIME_STATE',
                $this->runtime->hasFailed() ? 'Runtime has failed.' : 'Runtime has no recorded failure.',
                !$this->runtime->hasFailed(),
            ),
            new CliRuntimeDiagnostic(
                'CONFIGURATION_AVAILABLE',
                'Configuration repository is available.',
                true,
            ),
            new CliRuntimeDiagnostic(
                'CONFIGURATION_STATE',
                $this->configuration->isFrozen() ? 'Configuration is frozen.' : 'Configuration remains mutable.',
                true,
            ),
        ]);

        return new CliCommandResult(
            $report->healthy() ? CliExitCode::success() : CliExitCode::validationFailure(),
            $report->healthy() ? 'Runtime diagnostics passed.' : 'Runtime diagnostics failed.',
            $report->summary(),
        );
    }
}
