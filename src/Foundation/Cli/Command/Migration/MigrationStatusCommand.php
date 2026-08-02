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

final readonly class MigrationStatusCommand implements CliCommandInterface
{
    public function __construct(private CliMigrationOperations $operations) {}
    public function metadata(): CliCommandMetadata { return new CliCommandMetadata(new CliCommandName('migration:status'), 'Inspects migration integrity and pending state.', null, [], [], CliOperationalClass::inspection(), false, false); }
    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $report = $this->operations->runtime()->inspect();
        return new CliCommandResult(
            $report->isValid() ? CliExitCode::success() : CliExitCode::validationFailure(),
            $report->isValid() ? 'Migration state is valid.' : 'Migration integrity validation failed.',
            [
                'valid' => $report->isValid(),
                'missing_from_registry' => $report->missingFromRegistry(),
                'checksum_mismatches' => $report->checksumMismatches(),
                'pending' => $report->pending(),
            ],
        );
    }
}
