<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Installer;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Operations\CliInstallerOperations;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;

final readonly class InstallerAssessCommand implements CliCommandInterface
{
    public function __construct(private CliInstallerOperations $operations) {}
    public function metadata(): CliCommandMetadata { return new CliCommandMetadata(new CliCommandName('installer:assess'), 'Assesses installation requirements.', null, [], [], CliOperationalClass::validation(), false, false); }
    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $report = $this->operations->runtime()->assess($this->operations->request($invocation));
        return new CliCommandResult($report->canProceed() ? CliExitCode::success() : CliExitCode::requirementsNotSatisfied(), $report->canProceed() ? 'Installation requirements are satisfied.' : 'Installation requirements are not satisfied.', ['requirements' => $report->summary()]);
    }
}
