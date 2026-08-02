<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Command\Migration\MigrationRunCommand;
use Sif\Foundation\Cli\Command\Migration\MigrationStatusCommand;
use Sif\Foundation\Cli\Operations\CliMigrationOperations;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationHistoryStore;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationLock;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationTransactionManager;
use Sif\Foundation\Migration\Execution\MigrationExecutor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\Registry\MigrationRegistry;
use Sif\Foundation\Migration\Runtime\MigrationRuntime;
use Sif\Foundation\Migration\Selection\MigrationSelector;

final class CliMigrationInstallerCommandTest extends TestCase
{
    public function testMigrationStatusReportsValidEmptyRegistry(): void
    {
        $command = new MigrationStatusCommand($this->operations());
        $result = $command->execute(new CliInvocation(new CliCommandName('migration:status')));

        self::assertSame(0, $result->exitCode()->value());
        self::assertTrue($result->data()['valid']);
        self::assertSame([], $result->data()['pending']);
    }

    public function testMigrationRunFailsClosedWithoutAuthorization(): void
    {
        $command = new MigrationRunCommand($this->operations());
        $result = $command->execute(new CliInvocation(new CliCommandName('migration:run')));

        self::assertSame(5, $result->exitCode()->value());
        self::assertArrayHasKey('plan_fingerprint', $result->data());
    }

    private function operations(): CliMigrationOperations
    {
        $history = new InMemoryMigrationHistoryStore();
        $runtime = new MigrationRuntime(
            new MigrationRegistry(),
            $history,
            new MigrationSelector(),
            new MigrationExecutor(
                [],
                $history,
                new InMemoryMigrationLock(),
                new InMemoryMigrationTransactionManager(),
            ),
        );

        return new CliMigrationOperations(
            $runtime,
            static fn (
                CliInvocation $invocation,
                MigrationDirection $direction,
                MigrationExecutionMode $mode,
            ): MigrationRequest => new MigrationRequest($direction, $mode),
        );
    }
}
