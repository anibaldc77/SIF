<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Migration;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Operations\CliMigrationOperations;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;

final readonly class MigrationRollbackCommand implements CliCommandInterface
{
    public function __construct(private CliMigrationOperations $operations) {}
    public function metadata(): CliCommandMetadata { return new CliCommandMetadata(new CliCommandName('migration:rollback'), 'Executes an authorized reverse migration plan.', null, [], [], CliOperationalClass::mutation(), false, true); }
    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $request = $this->operations->request($invocation, MigrationDirection::down(), MigrationExecutionMode::apply());
        $plan = $this->operations->runtime()->plan($request);
        $authorization = $this->operations->authorization($plan, $invocation);
        if ($authorization === null || !$authorization->executionAllowed()) {
            return new CliCommandResult(CliExitCode::notAuthorized(), 'Migration rollback requires explicit authorization.', ['plan_fingerprint' => $plan->fingerprint()]);
        }
        $report = $this->operations->runtime()->execute($plan, $authorization);
        return new CliCommandResult($report->successful() ? CliExitCode::success() : CliExitCode::partialOrCompensated(), $report->successful() ? 'Migration rollback completed.' : 'Migration rollback was incomplete.', $report->summary());
    }
}
