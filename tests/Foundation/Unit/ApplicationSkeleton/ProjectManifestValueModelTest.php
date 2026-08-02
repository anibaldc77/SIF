<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ApplicationSkeleton;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidProjectManifestException;
use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifestSerializer;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Value\FirstRunState;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;

final class ProjectManifestValueModelTest extends TestCase
{
    public function testManifestIsNormalizedAndSerializedDeterministically(): void
    {
        $manifest = self::manifest();
        $array = $manifest->toArray();

        self::assertSame('sample-app', $array['project']['id']);
        self::assertSame(['development', 'production', 'testing'], $array['environments']);
        self::assertSame(['audit', 'cli'], $array['capabilities']);
        self::assertSame('cli', $array['entry_points'][0]['name']);
        self::assertSame('http', $array['entry_points'][1]['name']);

        $json = (new ProjectManifestSerializer())->toJson($manifest);
        self::assertStringContainsString('"schema_version": "1.0.0"', $json);
        self::assertStringEndsWith("\n", $json);
        self::assertSame($json, (new ProjectManifestSerializer())->toJson($manifest));
    }

    public function testPortablePathRejectsTraversalAbsoluteAndWindowsPaths(): void
    {
        foreach (['../secret', '/etc/passwd', 'C:/temp/app', 'app\\Models'] as $path) {
            try {
                new ProjectPath($path);
                self::fail(sprintf('Path "%s" should be rejected.', $path));
            } catch (InvalidSkeletonValueException) {
                self::assertTrue(true);
            }
        }
    }

    public function testOnlySkeletonOwnedPathsMayBeReplaced(): void
    {
        $this->expectException(InvalidProjectManifestException::class);

        new ProjectPathDefinition(
            new ProjectPath('app/Models'),
            SkeletonOwnership::UserOwned,
            SkeletonOverwritePolicy::Replace,
        );
    }

    public function testDuplicateEntryPointsAreRejected(): void
    {
        $this->expectException(InvalidProjectManifestException::class);

        new ProjectManifest(
            new ProjectIdentifier('sample-app'),
            'Sample Application',
            new ProjectNamespace('Sample\\Application'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [
                new ProjectEntryPoint('cli', new ProjectPath('sif')),
                new ProjectEntryPoint('cli', new ProjectPath('sif.bat')),
            ],
            ['development'],
            [new ProjectPathDefinition(new ProjectPath('app'), SkeletonOwnership::UserOwned)],
        );
    }

    public function testFirstRunStatesAreExplicit(): void
    {
        self::assertSame('uninitialized', FirstRunState::Uninitialized->value);
        self::assertSame('authorized', FirstRunState::Authorized->value);
        self::assertSame('completed', FirstRunState::Completed->value);
        self::assertSame('failed', FirstRunState::Failed->value);
    }

    private static function manifest(): ProjectManifest
    {
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
            ['production', 'development', 'testing', 'development'],
            [
                new ProjectPathDefinition(new ProjectPath('storage/logs'), SkeletonOwnership::RuntimeOwned),
                new ProjectPathDefinition(new ProjectPath('app'), SkeletonOwnership::UserOwned),
                new ProjectPathDefinition(
                    new ProjectPath('bootstrap/app.php'),
                    SkeletonOwnership::SkeletonOwned,
                    SkeletonOverwritePolicy::Replace,
                ),
            ],
            ['cli', 'audit', 'cli'],
        );
    }
}
