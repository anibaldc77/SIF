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

final readonly class InstallerRunCommand implements CliCommandInterface
{
    public function __construct(private CliInstallerOperations $operations) {}
    public function metadata(): CliCommandMetadata { return new CliCommandMetadata(new CliCommandName('installer:run'), 'Executes an authorized installation plan.', null, [], [], CliOperationalClass::mutation(), false, true); }
    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $request = $this->operations->request($invocation);
        $dryRun = $this->operations->runtime()->dryRun($request, $this->operations->mutations($request, $invocation));
        if (!$dryRun->executable()) {
            return new CliCommandResult(CliExitCode::requirementsNotSatisfied(), 'Installation requirements are not satisfied.', $dryRun->summary());
        }
        $authorization = $this->operations->authorization($dryRun, $invocation);
        if ($authorization === null || !$authorization->mutationAllowed()) {
            return new CliCommandResult(CliExitCode::notAuthorized(), 'Installation execution requires explicit authorization.', ['plan_fingerprint' => $dryRun->planFingerprint()]);
        }
        $report = $this->operations->runtime()->execute($dryRun, $authorization);
        return new CliCommandResult($report->isSuccessful() ? CliExitCode::success() : CliExitCode::partialOrCompensated(), $report->isSuccessful() ? 'Installation completed.' : 'Installation was incomplete or compensated.', $report->summary());
    }
}
