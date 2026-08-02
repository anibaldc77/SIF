<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ApplicationSkeleton;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Cli\ApplicationCreateCommand;
use Sif\Foundation\ApplicationSkeleton\Cli\ApplicationCreationAuthorization;
use Sif\Foundation\ApplicationSkeleton\Cli\ApplicationCreationOperations;
use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;

final class ApplicationCreateCommandTest extends TestCase
{
    public function testCommandPlansByDefaultAndExecutesOnlyWithMatchingAuthorization(): void
    {
        $filesystem = new AppCreateMemoryFilesystem();
        $authorization = null;
        $operations = new ApplicationCreationOperations(
            static fn (CliInvocation $invocation): ProjectManifest => self::manifest(),
            static fn (string $target): SkeletonFilesystemInterface => $filesystem,
            static function ($plan) use (&$authorization): ?ApplicationCreationAuthorization {
                return $authorization;
            },
        );
        $command = new ApplicationCreateCommand($operations);

        $planResult = $command->execute(new CliInvocation(new CliCommandName('app:create'), ['target']));
        self::assertSame(0, $planResult->exitCode()->value());
        self::assertFalse($planResult->data()['executed']);

        $fingerprint = $planResult->data()['plan_fingerprint'];
        self::assertIsString($fingerprint);
        $authorization = new ApplicationCreationAuthorization($fingerprint, true);

        $runResult = $command->execute(new CliInvocation(
            new CliCommandName('app:create'),
            ['target'],
            ['execute' => [true]],
        ));
        self::assertSame(0, $runResult->exitCode()->value());
        self::assertTrue($runResult->data()['executed']);
        self::assertTrue($filesystem->exists(new ProjectPath('composer.json')));
    }

    private static function manifest(): ProjectManifest
    {
        $directories = ['app','app/Commands','app/Models','app/Modules','app/Providers','bootstrap','config','database/migrations','public','resources','routes','storage/cache','storage/logs','storage/runtime','tests'];
        $files = ['bootstrap/app.php','bootstrap/cli.php','public/index.php','config/app.php','config/database.php','.env.example','.gitignore','composer.json','phpunit.xml','sif','sif.bat','sif.project.json'];
        $paths = [];
        foreach ($directories as $path) {
            $paths[] = new ProjectPathDefinition(new ProjectPath($path), str_starts_with($path, 'storage/') ? SkeletonOwnership::RuntimeOwned : SkeletonOwnership::UserOwned, SkeletonOverwritePolicy::Skip);
        }
        foreach ($files as $path) {
            $paths[] = new ProjectPathDefinition(new ProjectPath($path), SkeletonOwnership::SkeletonOwned, SkeletonOverwritePolicy::Replace);
        }
        return new ProjectManifest(new ProjectIdentifier('sample-app'),'Sample Application',new ProjectNamespace('Sample\\Application'),'1.0.0','1.0.0','^2.0','8.2.0',[new ProjectEntryPoint('http',new ProjectPath('public/index.php')),new ProjectEntryPoint('cli',new ProjectPath('sif'))],['development','testing','production'],$paths,['cli']);
    }
}

final class AppCreateMemoryFilesystem implements SkeletonFilesystemInterface
{
    /** @var array<string, string|null> */ private array $entries = [];
    public function exists(ProjectPath $path): bool { return array_key_exists($path->value(), $this->entries); }
    public function isFile(ProjectPath $path): bool { return $this->exists($path) && $this->entries[$path->value()] !== null; }
    public function isDirectory(ProjectPath $path): bool { return $this->exists($path) && $this->entries[$path->value()] === null; }
    public function read(ProjectPath $path): string { return $this->entries[$path->value()] ?? ''; }
    public function createDirectory(ProjectPath $path): void { $this->entries[$path->value()] = null; }
    public function write(ProjectPath $path, string $content): void { $this->entries[$path->value()] = $content; }
}
