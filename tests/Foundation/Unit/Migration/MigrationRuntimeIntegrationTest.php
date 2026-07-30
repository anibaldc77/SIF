<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Migration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Environment;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationHistoryStore;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationLock;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationTransactionManager;
use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorization;
use Sif\Foundation\Migration\Contracts\MigrationOperationHandlerInterface;
use Sif\Foundation\Migration\Execution\MigrationExecutor;
use Sif\Foundation\Migration\Execution\MigrationOperationResult;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\Registry\MigrationRegistry;
use Sif\Foundation\Migration\Runtime\MigrationRuntime;
use Sif\Foundation\Migration\Runtime\RuntimeMigrationServiceProvider;
use Sif\Foundation\Migration\Selection\MigrationSelector;

final class MigrationRuntimeIntegrationTest extends TestCase
{
    public function testBootstrapWithoutMigrationRuntimeRemainsCompatible(): void
    {
        $application = (new Bootstrap())->createApplication(Environment::testing());

        self::assertNull($application->migrations());
        self::assertFalse($application->providers()->has(RuntimeMigrationServiceProvider::class));
        self::assertFalse($application->hasCapability('migration'));
    }

    public function testBootstrapPublishesMigrationRuntimeWhenConfigured(): void
    {
        $runtime = $this->runtime();
        $application = (new Bootstrap(migrations: $runtime))->createApplication(Environment::testing());

        self::assertSame($runtime, $application->migrations());
        self::assertTrue($application->providers()->has(RuntimeMigrationServiceProvider::class));
        self::assertFalse($application->hasCapability('migration'));
        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->hasCapability('migration'));
    }

    public function testRuntimePlansAndExecutesAuthorizedMigration(): void
    {
        $runtime = $this->runtime();
        $request = new MigrationRequest(MigrationDirection::up(), MigrationExecutionMode::apply());
        $plan = $runtime->plan($request);
        $authorization = new MigrationExecutionAuthorization(
            'runtime-test',
            $plan->fingerprint(),
            $plan->direction(),
            $plan->mode(),
            true,
        );

        $report = $runtime->execute($plan, $authorization);

        self::assertTrue($report->successful());
        self::assertSame(['foundation.create'], $runtime->history()->identifiers());
        self::assertTrue($runtime->inspect()->isValid());
    }

    private function runtime(): MigrationRuntime
    {
        $descriptor = new MigrationDescriptor(
            new MigrationId('foundation.create'),
            new MigrationChecksum('sha256', str_repeat('a', 64)),
            reversible: true,
        );
        $history = new InMemoryMigrationHistoryStore();
        $handler = new class implements MigrationOperationHandlerInterface {
            public function supports(MigrationDescriptor $migration): bool { return true; }
            public function execute(MigrationDescriptor $migration, MigrationDirection $direction): MigrationOperationResult { return MigrationOperationResult::success(); }
        };
        $executor = new MigrationExecutor(
            [$handler],
            $history,
            new InMemoryMigrationLock(),
            new InMemoryMigrationTransactionManager(),
        );

        return new MigrationRuntime(
            new MigrationRegistry([$descriptor]),
            $history,
            new MigrationSelector(),
            $executor,
        );
    }
}
