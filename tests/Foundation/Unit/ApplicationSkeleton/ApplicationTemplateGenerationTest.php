<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ApplicationSkeleton;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Bootstrap\ApplicationTemplateBlueprintFactory;
use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidProjectManifestException;
use Sif\Foundation\ApplicationSkeleton\Exceptions\TemplateRenderingException;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Template\ApplicationTemplate;
use Sif\Foundation\ApplicationSkeleton\Template\ApplicationTemplateRenderer;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;

final class ApplicationTemplateGenerationTest extends TestCase
{
    public function testFactoryBuildsDeterministicBootstrapAndEnvironmentBlueprint(): void
    {
        $factory = new ApplicationTemplateBlueprintFactory();
        $first = $factory->create(self::manifest());
        $second = $factory->create(self::manifest());

        self::assertSame($first->fingerprint(), $second->fingerprint());
        self::assertCount(27, $first->artifacts());

        $files = [];
        foreach ($first->artifacts() as $artifact) {
            if ($artifact->type() === SkeletonArtifactType::File) {
                $files[$artifact->path()->path()->value()] = $artifact->content();
            }
        }

        self::assertArrayHasKey('bootstrap/app.php', $files);
        self::assertArrayHasKey('bootstrap/cli.php', $files);
        self::assertArrayHasKey('public/index.php', $files);
        self::assertArrayHasKey('.env.example', $files);
        self::assertArrayHasKey('composer.json', $files);
        self::assertStringContainsString('Sample\\\\Application\\\\', (string) $files['composer.json']);
        self::assertStringContainsString('APP_ENV=development', (string) $files['.env.example']);
        self::assertStringNotContainsString("\r", implode('', array_values($files)));
    }

    public function testGeneratedFilesMustBeSkeletonOwned(): void
    {
        $manifest = self::manifest([
            'composer.json' => new ProjectPathDefinition(
                new ProjectPath('composer.json'),
                SkeletonOwnership::UserOwned,
                SkeletonOverwritePolicy::Skip,
            ),
        ]);

        $this->expectException(InvalidProjectManifestException::class);
        (new ApplicationTemplateBlueprintFactory())->create($manifest);
    }

    public function testRendererRejectsMissingAndUnknownVariables(): void
    {
        $renderer = new ApplicationTemplateRenderer();
        $template = new ApplicationTemplate('sample', "Hello {{name}}\n");

        try {
            $renderer->render($template, []);
            self::fail('Missing variables must be rejected.');
        } catch (TemplateRenderingException) {
            self::assertTrue(true);
        }

        $this->expectException(TemplateRenderingException::class);
        $renderer->render($template, ['name' => 'SIF', 'unused' => 'value']);
    }

    public function testManifestMustDeclareEveryRequiredTemplatePath(): void
    {
        $manifest = self::manifest([], ['sif.bat']);

        $this->expectException(InvalidProjectManifestException::class);
        (new ApplicationTemplateBlueprintFactory())->create($manifest);
    }

    /**
     * @param array<string, ProjectPathDefinition> $replacements
     * @param list<string> $excluded
     */
    private static function manifest(array $replacements = [], array $excluded = []): ProjectManifest
    {
        $directoryPaths = [
            'app', 'app/Commands', 'app/Models', 'app/Modules', 'app/Providers',
            'bootstrap', 'config', 'database/migrations', 'public', 'resources',
            'routes', 'storage/cache', 'storage/logs', 'storage/runtime', 'tests',
        ];
        $filePaths = [
            'bootstrap/app.php', 'bootstrap/cli.php', 'public/index.php',
            'config/app.php', 'config/database.php', '.env.example', '.gitignore',
            'composer.json', 'phpunit.xml', 'sif', 'sif.bat', 'sif.project.json',
        ];

        $paths = [];
        foreach ($directoryPaths as $path) {
            if (!in_array($path, $excluded, true)) {
                $paths[$path] = new ProjectPathDefinition(
                    new ProjectPath($path),
                    str_starts_with($path, 'storage/')
                        ? SkeletonOwnership::RuntimeOwned
                        : SkeletonOwnership::UserOwned,
                    SkeletonOverwritePolicy::Skip,
                );
            }
        }
        foreach ($filePaths as $path) {
            if (!in_array($path, $excluded, true)) {
                $paths[$path] = new ProjectPathDefinition(
                    new ProjectPath($path),
                    SkeletonOwnership::SkeletonOwned,
                    SkeletonOverwritePolicy::Replace,
                );
            }
        }
        foreach ($replacements as $path => $definition) {
            $paths[$path] = $definition;
        }

        return new ProjectManifest(
            new ProjectIdentifier('sample-app'),
            'Sample Application',
            new ProjectNamespace('Sample\\Application'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [
                new ProjectEntryPoint('http', new ProjectPath('public/index.php')),
                new ProjectEntryPoint('cli', new ProjectPath('sif')),
            ],
            ['development', 'testing', 'production'],
            array_values($paths),
            ['cli', 'configuration'],
        );
    }
}
