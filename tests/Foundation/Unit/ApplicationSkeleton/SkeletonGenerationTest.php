<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ApplicationSkeleton;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifact;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonBlueprint;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonGenerationAction;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonGenerationExecutor;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonGenerationPlanner;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;

final class SkeletonGenerationTest extends TestCase
{
    public function testPlannerProducesDeterministicCreationPlan(): void
    {
        $filesystem = new MemorySkeletonFilesystem();
        $plan = (new SkeletonGenerationPlanner($filesystem))->plan(self::blueprint());

        self::assertTrue($plan->executable());
        self::assertSame(
            ['create-directory', 'create-file', 'create-file'],
            array_map(
                static fn ($entry): string => $entry->action()->value,
                $plan->entries(),
            ),
        );
        self::assertSame($plan->fingerprint(), (new SkeletonGenerationPlanner($filesystem))->plan(self::blueprint())->fingerprint());
    }

    public function testExecutionIsIdempotentWhenGeneratedContentIsUnchanged(): void
    {
        $filesystem = new MemorySkeletonFilesystem();
        $planner = new SkeletonGenerationPlanner($filesystem);
        $executor = new SkeletonGenerationExecutor($filesystem);

        $executor->execute($planner->plan(self::blueprint()));
        $secondPlan = $planner->plan(self::blueprint());

        self::assertTrue($secondPlan->executable());
        self::assertSame(
            [SkeletonGenerationAction::Skip, SkeletonGenerationAction::Skip, SkeletonGenerationAction::Skip],
            array_map(static fn ($entry): SkeletonGenerationAction => $entry->action(), $secondPlan->entries()),
        );
        self::assertSame('<?php' . "\n", $filesystem->read(new ProjectPath('bootstrap/app.php')));
    }

    public function testFailPolicyReportsConflictWithoutWriting(): void
    {
        $filesystem = new MemorySkeletonFilesystem();
        $filesystem->write(new ProjectPath('sif.project.json'), "different\n");

        $plan = (new SkeletonGenerationPlanner($filesystem))->plan(self::blueprint());
        $entry = $plan->entries()[2];

        self::assertFalse($plan->executable());
        self::assertSame(SkeletonGenerationAction::Conflict, $entry->action());
        self::assertSame('existing-content-differs', $entry->reason());
    }

    public function testReplacePolicyOnlyReplacesSkeletonOwnedFile(): void
    {
        $filesystem = new MemorySkeletonFilesystem();
        $filesystem->write(new ProjectPath('bootstrap/app.php'), "old\n");

        $planner = new SkeletonGenerationPlanner($filesystem);
        $plan = $planner->plan(self::blueprint());

        self::assertSame(SkeletonGenerationAction::ReplaceFile, $plan->entries()[1]->action());
        (new SkeletonGenerationExecutor($filesystem))->execute($plan);
        self::assertSame('<?php' . "\n", $filesystem->read(new ProjectPath('bootstrap/app.php')));
    }

    private static function blueprint(): SkeletonBlueprint
    {
        $app = new ProjectPathDefinition(
            new ProjectPath('app'),
            SkeletonOwnership::UserOwned,
            SkeletonOverwritePolicy::Skip,
        );
        $bootstrap = new ProjectPathDefinition(
            new ProjectPath('bootstrap/app.php'),
            SkeletonOwnership::SkeletonOwned,
            SkeletonOverwritePolicy::Replace,
        );
        $manifestPath = new ProjectPathDefinition(
            new ProjectPath('sif.project.json'),
            SkeletonOwnership::SkeletonOwned,
            SkeletonOverwritePolicy::Fail,
        );

        $manifest = new ProjectManifest(
            new ProjectIdentifier('sample-app'),
            'Sample Application',
            new ProjectNamespace('Sample\\Application'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [new ProjectEntryPoint('cli', new ProjectPath('sif'))],
            ['development'],
            [$app, $bootstrap, $manifestPath],
        );

        return new SkeletonBlueprint($manifest, [
            new SkeletonArtifact($manifestPath, SkeletonArtifactType::File, "{}\n"),
            new SkeletonArtifact($bootstrap, SkeletonArtifactType::File, '<?php' . "\n"),
            new SkeletonArtifact($app, SkeletonArtifactType::Directory),
        ]);
    }
}

final class MemorySkeletonFilesystem implements SkeletonFilesystemInterface
{
    /** @var array<string, string> */
    private array $files = [];

    /** @var array<string, true> */
    private array $directories = [];

    public function exists(ProjectPath $path): bool
    {
        return isset($this->files[$path->value()]) || isset($this->directories[$path->value()]);
    }

    public function isFile(ProjectPath $path): bool
    {
        return isset($this->files[$path->value()]);
    }

    public function isDirectory(ProjectPath $path): bool
    {
        return isset($this->directories[$path->value()]);
    }

    public function read(ProjectPath $path): string
    {
        return $this->files[$path->value()];
    }

    public function createDirectory(ProjectPath $path): void
    {
        $this->directories[$path->value()] = true;
    }

    public function write(ProjectPath $path, string $content): void
    {
        $this->files[$path->value()] = $content;
    }
}
