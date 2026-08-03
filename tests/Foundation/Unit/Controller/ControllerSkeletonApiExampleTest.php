<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Example\ExampleApiApplicationBlueprintFactory;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Template\ControllerApiTemplateFactory;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;

final class ControllerSkeletonApiExampleTest extends TestCase
{
    public function testControllerApiArtifactsAreDeterministicAndUserOwned(): void
    {
        $manifest = self::manifest();
        $factory = new ControllerApiTemplateFactory();
        $first = $factory->artifacts($manifest);
        $second = $factory->artifacts($manifest);

        self::assertSame(
            array_map(static fn ($artifact): array => $artifact->summary(), $first),
            array_map(static fn ($artifact): array => $artifact->summary(), $second),
        );
        self::assertCount(6, $first);

        $files = [];
        foreach ($first as $artifact) {
            self::assertSame(SkeletonOwnership::UserOwned, $artifact->path()->ownership());
            self::assertSame(SkeletonOverwritePolicy::Fail, $artifact->path()->overwritePolicy());
            if ($artifact->type() === SkeletonArtifactType::File) {
                $files[$artifact->path()->path()->value()] = (string) $artifact->content();
            }
        }

        self::assertStringContainsString("'/api/health'", $files['routes/api.php']);
        self::assertStringContainsString("'api.health'", $files['routes/api.php']);
        self::assertStringContainsString('final readonly class HealthController', $files['app/Controllers/HealthController.php']);
        self::assertStringContainsString("'controller.health' => HealthController::class", $files['config/controllers.php']);
        self::assertStringNotContainsString("\r", implode('', $files));
    }

    public function testExampleApiBlueprintExtendsCanonicalApplicationSkeleton(): void
    {
        $blueprint = (new ExampleApiApplicationBlueprintFactory())->create(self::manifest());
        $paths = array_map(
            static fn ($artifact): string => $artifact->path()->path()->value(),
            $blueprint->artifacts(),
        );

        self::assertContains('public/index.php', $paths);
        self::assertContains('app/Controllers/HealthController.php', $paths);
        self::assertContains('app/Providers/ControllerServiceProvider.php', $paths);
        self::assertContains('routes/api.php', $paths);
        self::assertContains('config/controllers.php', $paths);
        self::assertContains('tests/Feature/HealthApiTest.php', $paths);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $blueprint->fingerprint());
    }

    private static function manifest(): ProjectManifest
    {
        $directoryPaths = [
            'app', 'app/Commands', 'app/Controllers', 'app/Models', 'app/Modules', 'app/Providers',
            'bootstrap', 'config', 'database/migrations', 'public', 'resources',
            'routes', 'storage/cache', 'storage/logs', 'storage/runtime', 'tests',
        ];
        $skeletonFiles = [
            'bootstrap/app.php', 'bootstrap/cli.php', 'public/index.php',
            'config/app.php', 'config/database.php', '.env.example', '.gitignore',
            'composer.json', 'phpunit.xml', 'sif', 'sif.bat', 'sif.project.json',
        ];
        $apiFiles = [
            'app/Controllers/HealthController.php',
            'app/Providers/ControllerServiceProvider.php',
            'routes/api.php',
            'config/controllers.php',
            'tests/Feature/HealthApiTest.php',
        ];

        $paths = [];
        foreach ($directoryPaths as $path) {
            $paths[] = new ProjectPathDefinition(
                new ProjectPath($path),
                str_starts_with($path, 'storage/') ? SkeletonOwnership::RuntimeOwned : SkeletonOwnership::UserOwned,
                str_starts_with($path, 'storage/') ? SkeletonOverwritePolicy::Skip : SkeletonOverwritePolicy::Fail,
            );
        }
        foreach ($skeletonFiles as $path) {
            $paths[] = new ProjectPathDefinition(
                new ProjectPath($path),
                SkeletonOwnership::SkeletonOwned,
                SkeletonOverwritePolicy::Replace,
            );
        }
        foreach ($apiFiles as $path) {
            $paths[] = new ProjectPathDefinition(
                new ProjectPath($path),
                SkeletonOwnership::UserOwned,
                SkeletonOverwritePolicy::Fail,
            );
        }

        return new ProjectManifest(
            new ProjectIdentifier('sample-api'),
            'Sample API',
            new ProjectNamespace('Sample\\Api'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [
                new ProjectEntryPoint('http', new ProjectPath('public/index.php')),
                new ProjectEntryPoint('cli', new ProjectPath('sif')),
            ],
            ['development', 'testing', 'production'],
            $paths,
            ['cli', 'configuration', 'http', 'controller-api'],
        );
    }
}
