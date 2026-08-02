<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ApplicationSkeleton;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\FirstRun\ApplicationFirstRunAuthorization;
use Sif\Foundation\ApplicationSkeleton\FirstRun\ApplicationFirstRunCoordinator;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifact;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonBlueprint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;

final class ApplicationFirstRunValidationTest extends TestCase
{
    public function testFirstRunPlansThenExecutesAndValidates(): void
    {
        $path = new ProjectPathDefinition(
            new ProjectPath('bootstrap/app.php'),
            SkeletonOwnership::SkeletonOwned,
            SkeletonOverwritePolicy::Replace,
        );
        $manifest = new ProjectManifest(
            new ProjectIdentifier('sample-app'),
            'Sample App',
            new ProjectNamespace('Sample\\Application'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [new ProjectEntryPoint('http', new ProjectPath('bootstrap/app.php'))],
            ['development'],
            [$path],
            [],
        );
        $blueprint = new SkeletonBlueprint($manifest, [
            new SkeletonArtifact($path, SkeletonArtifactType::File, "<?php\n"),
        ]);
        $filesystem = new FirstRunMemoryFilesystem();
        $coordinator = new ApplicationFirstRunCoordinator($filesystem);

        $planned = $coordinator->run($blueprint);
        self::assertFalse($planned->completed());
        self::assertFalse($filesystem->exists(new ProjectPath('bootstrap/app.php')));

        $completed = $coordinator->run(
            $blueprint,
            new ApplicationFirstRunAuthorization($planned->summary()['plan_fingerprint'], true),
        );

        self::assertTrue($completed->completed());
        self::assertTrue($filesystem->exists(new ProjectPath('bootstrap/app.php')));
        self::assertTrue($completed->summary()['validation']['valid']);
    }
}

final class FirstRunMemoryFilesystem implements SkeletonFilesystemInterface
{
    /** @var array<string, string> */
    private array $files = [];
    /** @var array<string, true> */
    private array $directories = [];

    public function exists(ProjectPath $path): bool { return isset($this->files[$path->value()]) || isset($this->directories[$path->value()]); }
    public function isFile(ProjectPath $path): bool { return isset($this->files[$path->value()]); }
    public function isDirectory(ProjectPath $path): bool { return isset($this->directories[$path->value()]); }
    public function read(ProjectPath $path): string { return $this->files[$path->value()] ?? ''; }
    public function createDirectory(ProjectPath $path): void { $this->directories[$path->value()] = true; }
    public function write(ProjectPath $path, string $content): void { $this->files[$path->value()] = $content; }
}
