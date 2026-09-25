<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ApplicationSkeleton;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Template\ApplicationCodeTemplateFactory;
use Sif\Foundation\ApplicationSkeleton\Template\ModelTemplateOptions;
use Sif\Foundation\ApplicationSkeleton\Value\ApplicationCodeName;
use Sif\Foundation\ApplicationSkeleton\Value\MigrationTemplateName;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;

final class ApplicationCodeTemplateGenerationTest extends TestCase
{
    public function testGeneratedMigrationExecutesThroughCanonicalRuntimeAndDetectsChanges(): void
    {
        $artifact = (new ApplicationCodeTemplateFactory())->migration(
            $this->manifest(),
            new MigrationTemplateName('20260802191200_create_invoices'),
            new ApplicationCodeName('CreateInvoices'),
        );
        $path = sys_get_temp_dir() . '/sif-migration-' . bin2hex(random_bytes(8)) . '.php';
        try {
            file_put_contents($path, $artifact->content());
            $definition = require $path;
            self::assertIsArray($definition);
            $descriptor = $definition['descriptor'];
            $operation = $definition['operation'];
            self::assertInstanceOf(\Sif\Foundation\Migration\MigrationDescriptor::class, $descriptor);
            self::assertInstanceOf(\Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperation::class, $operation);
            self::assertFalse($descriptor->reversible());
            self::assertSame($descriptor->id()->value(), $operation->id()->value());

            $pdo = $this->createMock(\PDO::class);
            $statement = $this->createMock(\PDOStatement::class);
            $pdo->expects(self::once())->method('prepare')->with('SELECT 1')->willReturn($statement);
            $statement->expects(self::once())->method('execute')->with([])->willReturn(true);
            $statement->expects(self::once())->method('closeCursor')->willReturn(true);
            $connection = new \Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection(
                $pdo,
                new \Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionName('template-test'),
                \Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform::sqlserver(),
                \Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionOwnership::external(),
                \Sif\Foundation\Migration\Pdo\Platform\PdoMigrationCapabilities::sqlserver(),
            );
            $history = new \Sif\Foundation\Migration\Adapter\InMemoryMigrationHistoryStore();
            $runtime = new \Sif\Foundation\Migration\Runtime\MigrationRuntime(
                new \Sif\Foundation\Migration\Registry\MigrationRegistry([$descriptor]),
                $history,
                new \Sif\Foundation\Migration\Selection\MigrationSelector(),
                new \Sif\Foundation\Migration\Execution\MigrationExecutor(
                    [new \Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationHandler(
                        $connection,
                        new \Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationCatalog([$operation]),
                    )],
                    $history,
                    new \Sif\Foundation\Migration\Adapter\InMemoryMigrationLock(),
                    new \Sif\Foundation\Migration\Adapter\InMemoryMigrationTransactionManager(),
                ),
            );
            $request = new \Sif\Foundation\Migration\MigrationRequest(
                \Sif\Foundation\Migration\MigrationDirection::up(),
                \Sif\Foundation\Migration\MigrationExecutionMode::apply(),
            );
            $plan = $runtime->plan($request);
            $authorization = new \Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorization(
                'template-test',
                $plan->fingerprint(),
                $plan->direction(),
                $plan->mode(),
                true,
            );
            self::assertTrue($runtime->execute($plan, $authorization)->successful());
            self::assertSame([$descriptor->id()->value()], $runtime->history()->identifiers());
            self::assertTrue($runtime->inspect()->isValid());

            file_put_contents($path, str_replace("\n", "\r\n", (string) $artifact->content()));
            $crlf = require $path;
            self::assertTrue($descriptor->checksum()->equals($crlf['descriptor']->checksum()));
            file_put_contents($path, str_replace('SELECT 1', 'SELECT 2', (string) $artifact->content()));
            $changed = require $path;
            self::assertFalse($descriptor->checksum()->equals($changed['descriptor']->checksum()));
            self::assertFalse((new \Sif\Foundation\Migration\History\MigrationIntegrityChecker())->inspect(
                new \Sif\Foundation\Migration\Registry\MigrationRegistry([$changed['descriptor']]),
                $runtime->history(),
            )->isValid());
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testGeneratesModuleModelAndMigrationTemplatesDeterministically(): void
    {
        $manifest = $this->manifest();
        $factory = new ApplicationCodeTemplateFactory();

        $module = $factory->moduleServiceProvider($manifest, new ApplicationCodeName('Billing'));
        $model = $factory->model(
            $manifest,
            new ApplicationCodeName('Invoice'),
            'invoices',
            new ModelTemplateOptions(['tenant_id', 'id'], true, true),
        );
        $migration = $factory->migration(
            $manifest,
            new MigrationTemplateName('20260802191200_create_invoices'),
            new ApplicationCodeName('CreateInvoices'),
        );

        self::assertStringContainsString('namespace Sample\\Application\\Modules\\Billing;', (string) $module->content());
        self::assertStringContainsString("public const TABLE = 'invoices';", (string) $model->content());
        self::assertStringContainsString("'tenant_id'", (string) $model->content());
        self::assertStringContainsString('public const USE_SOFT_DELETES = true;', (string) $model->content());
        self::assertStringContainsString("new MigrationId('20260802191200_create_invoices')", (string) $migration->content());
        self::assertSame(hash('sha256', (string) $model->content()), $model->fingerprint());
        self::assertStringNotContainsString("\r", (string) $module->content());
    }

    private function manifest(): ProjectManifest
    {
        $paths = [
            new ProjectPathDefinition(new ProjectPath('public/index.php'), SkeletonOwnership::SkeletonOwned),
            new ProjectPathDefinition(new ProjectPath('app/Modules/Billing/BillingServiceProvider.php'), SkeletonOwnership::UserOwned, SkeletonOverwritePolicy::Fail),
            new ProjectPathDefinition(new ProjectPath('app/Models/Invoice.php'), SkeletonOwnership::UserOwned, SkeletonOverwritePolicy::Fail),
            new ProjectPathDefinition(new ProjectPath('database/migrations/20260802191200_create_invoices.php'), SkeletonOwnership::UserOwned, SkeletonOverwritePolicy::Fail),
        ];

        return new ProjectManifest(
            new ProjectIdentifier('sample-app'),
            'Sample App',
            new ProjectNamespace('Sample\\Application'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [new ProjectEntryPoint('http', new ProjectPath('public/index.php'))],
            ['development'],
            $paths,
        );
    }
}
