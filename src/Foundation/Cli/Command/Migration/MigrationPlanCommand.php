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

final readonly class MigrationPlanCommand implements CliCommandInterface
{
    public function __construct(private CliMigrationOperations $operations) {}
    public function metadata(): CliCommandMetadata { return new CliCommandMetadata(new CliCommandName('migration:plan'), 'Builds a migration dry-run plan.', null, [], [], CliOperationalClass::planning(), false, false); }
    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $request = $this->operations->request($invocation, MigrationDirection::up(), MigrationExecutionMode::dryRun());
        $report = $this->operations->runtime()->dryRun($request);
        return new CliCommandResult(CliExitCode::success(), 'Migration plan generated.', $report->summary());
    }
}
