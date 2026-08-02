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

final readonly class InstallerPlanCommand implements CliCommandInterface
{
    public function __construct(private CliInstallerOperations $operations) {}
    public function metadata(): CliCommandMetadata { return new CliCommandMetadata(new CliCommandName('installer:plan'), 'Builds an installation dry-run plan.', null, [], [], CliOperationalClass::planning(), false, false); }
    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $request = $this->operations->request($invocation);
        $dryRun = $this->operations->runtime()->dryRun($request, $this->operations->mutations($request, $invocation));
        return new CliCommandResult($dryRun->executable() ? CliExitCode::success() : CliExitCode::requirementsNotSatisfied(), $dryRun->executable() ? 'Installation plan generated.' : 'Installation plan is not executable.', $dryRun->summary());
    }
}
