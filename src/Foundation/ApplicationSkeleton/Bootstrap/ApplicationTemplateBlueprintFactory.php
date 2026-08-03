<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Bootstrap;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidProjectManifestException;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifact;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonBlueprint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifestSerializer;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Template\ApplicationTemplate;
use Sif\Foundation\ApplicationSkeleton\Template\ApplicationTemplateRenderer;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;

final readonly class ApplicationTemplateBlueprintFactory
{
    public function __construct(
        private ApplicationTemplateRenderer $renderer = new ApplicationTemplateRenderer(),
        private ProjectManifestSerializer $manifestSerializer = new ProjectManifestSerializer(),
    ) {
    }

    public function create(ProjectManifest $manifest): SkeletonBlueprint
    {
        $namespace = $manifest->namespace()->value();
        $namespacePrefix = $namespace . '\\';
        $projectId = $manifest->identifier()->value();

        $files = [
            'bootstrap/app.php' => $this->render('bootstrap-app', <<<'TPL'
<?php

declare(strict_types=1);

use Sif\Foundation\Bootstrap;

return new Bootstrap(
    configurationSources: [
        dirname(__DIR__) . '/config/app.php',
        dirname(__DIR__) . '/config/database.php',
    ],
    dotenvSource: dirname(__DIR__) . '/.env',
);
TPL, []),
            'bootstrap/cli.php' => $this->render('bootstrap-cli', <<<'TPL'
<?php

declare(strict_types=1);

use Sif\Foundation\Cli\Runtime\DefaultCliRuntimeFactory;

return (new DefaultCliRuntimeFactory())->create();
TPL, []),
            'public/index.php' => $this->render('public-index', <<<'TPL'
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Sif\Foundation\Contracts\BootstrapInterface;
use Sif\Foundation\Environment;
use Sif\Foundation\Http\Transport\NativeResponseEmitter;

$bootstrap = require dirname(__DIR__) . '/bootstrap/app.php';
if (!$bootstrap instanceof BootstrapInterface) {
    throw new RuntimeException('bootstrap/app.php must return a BootstrapInterface instance.');
}

$application = $bootstrap->createApplication(Environment::production());
$http = $application->http();
if ($http === null) {
    throw new RuntimeException('The application does not provide an HTTP runtime.');
}

$http->runNative(new NativeResponseEmitter());
TPL, []),
            'config/app.php' => $this->render('config-app', <<<'TPL'
<?php

declare(strict_types=1);

return [
    'name' => '{{project_name}}',
    'environment' => '${APP_ENV:-development}',
    'debug' => '${APP_DEBUG:-false}',
];
TPL, ['project_name' => $this->phpString($manifest->name())]),
            'config/database.php' => $this->render('config-database', <<<'TPL'
<?php

declare(strict_types=1);

return [
    'driver' => '${DB_DRIVER:-sqlite}',
    'dsn' => '${DB_DSN:-}',
    'username' => '${DB_USERNAME:-}',
    'password' => '${DB_PASSWORD:-}',
];
TPL, []),
            '.env.example' => $this->render('dotenv-example', <<<'TPL'
APP_ENV=development
APP_DEBUG=false
DB_DRIVER=sqlite
DB_DSN=
DB_USERNAME=
DB_PASSWORD=
TPL, []),
            '.gitignore' => $this->render('gitignore', <<<'TPL'
/.env
/vendor/
/storage/cache/*
/storage/logs/*
/storage/runtime/*
!/storage/cache/.gitkeep
!/storage/logs/.gitkeep
!/storage/runtime/.gitkeep
/.phpunit.result.cache
TPL, []),
            'composer.json' => $this->composerJson($projectId, $manifest->name(), $namespacePrefix, $manifest),
            'phpunit.xml' => $this->render('phpunit', <<<'TPL'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true" cacheResult="true">
    <testsuites>
        <testsuite name="Application">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing" force="true"/>
    </php>
</phpunit>
TPL, []),
            'sif' => $this->render('launcher-unix', <<<'TPL'
#!/usr/bin/env sh
set -eu
exec php "$(dirname "$0")/vendor/sif/runtime-foundation/bin/sif" "$@"
TPL, []),
            'sif.bat' => $this->render('launcher-windows', <<<'TPL'
@echo off
setlocal
php "%~dp0vendor\sif\runtime-foundation\bin\sif" %*
exit /b %ERRORLEVEL%
TPL, []),
            'sif.project.json' => $this->manifestSerializer->toJson($manifest),
        ];

        $directories = [
            'app',
            'app/Commands',
            'app/Models',
            'app/Modules',
            'app/Providers',
            'bootstrap',
            'config',
            'database/migrations',
            'public',
            'resources',
            'routes',
            'storage/cache',
            'storage/logs',
            'storage/runtime',
            'tests',
        ];

        $artifacts = [];
        foreach ($directories as $path) {
            $artifacts[] = new SkeletonArtifact(
                $this->definition($manifest, $path),
                SkeletonArtifactType::Directory,
            );
        }
        foreach ($files as $path => $content) {
            $definition = $this->definition($manifest, $path);
            if ($definition->ownership() !== SkeletonOwnership::SkeletonOwned) {
                throw new InvalidProjectManifestException(sprintf(
                    'Generated template path "%s" must be skeleton-owned.',
                    $path,
                ));
            }
            $artifacts[] = new SkeletonArtifact($definition, SkeletonArtifactType::File, $this->withFinalNewline($content));
        }

        return new SkeletonBlueprint($manifest, $artifacts);
    }

    /** @param array<string, string> $variables */
    private function render(string $name, string $template, array $variables): string
    {
        return $this->renderer->render(new ApplicationTemplate($name, $template), $variables);
    }

    private function composerJson(
        string $projectId,
        string $projectName,
        string $namespacePrefix,
        ProjectManifest $manifest,
    ): string {
        $payload = [
            'name' => 'application/' . $projectId,
            'description' => $projectName,
            'type' => 'project',
            'license' => 'proprietary',
            'require' => [
                'php' => '^' . $manifest->minimumPhpVersion(),
                'sif/runtime-foundation' => $manifest->sifConstraint(),
            ],
            'autoload' => [
                'psr-4' => [
                    $namespacePrefix => 'app/',
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    $namespacePrefix . 'Tests\\' => 'tests/',
                ],
            ],
            'scripts' => [
                'test' => '@php vendor/bin/phpunit',
                'sif' => '@php vendor/sif/runtime-foundation/bin/sif',
            ],
            'minimum-stability' => 'stable',
            'prefer-stable' => true,
        ];

        return json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ) . "\n";
    }

    private function definition(ProjectManifest $manifest, string $path): ProjectPathDefinition
    {
        $definitions = $manifest->paths();
        if (!isset($definitions[$path])) {
            throw new InvalidProjectManifestException(sprintf(
                'Required application template path "%s" is not declared by the project manifest.',
                $path,
            ));
        }

        return $definitions[$path];
    }

    private function phpString(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }

    private function withFinalNewline(string $content): string
    {
        return str_ends_with($content, "\n") ? $content : $content . "\n";
    }
}
