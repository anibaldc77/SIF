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
        self::assertStringContainsString("return '20260802191200_create_invoices';", (string) $migration->content());
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
