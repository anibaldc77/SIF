<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Environment;
use Sif\Foundation\Resources\Contribution\CompiledResourceContributionPlan;
use Sif\Foundation\Resources\Filesystem\AuthorizedResourceRoot;
use Sif\Foundation\Resources\Planning\ResourceManagementPlan;
use Sif\Foundation\Resources\Registry\CompiledResourceRegistry;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourceRootIdentifier;
use Sif\Foundation\Resources\Runtime\RuntimeResourceManagementServiceProvider;

final class ResourceManagementRuntimeIntegrationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sif-resource-runtime-' . bin2hex(random_bytes(6));
        mkdir($this->root, 0777, true);
        mkdir($this->root . DIRECTORY_SEPARATOR . 'assets', 0777, true);
        file_put_contents($this->root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'app.js', 'runtime');
    }

    protected function tearDown(): void
    {
        @unlink($this->root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'app.js');
        @rmdir($this->root . DIRECTORY_SEPARATOR . 'assets');
        @rmdir($this->root);
    }

    public function testBootstrapWithoutPlanRemainsCompatible(): void
    {
        $application = (new Bootstrap())->createApplication(Environment::testing());

        self::assertNull($application->resourceManagementPlan());
        self::assertNull($application->resourcePathResolver());
        self::assertFalse($application->providers()->has(RuntimeResourceManagementServiceProvider::class));
        self::assertFalse($application->hasCapability('resource-management'));
    }

    public function testBootstrapPublishesPlanAndProviderWhenConfigured(): void
    {
        $plan = $this->plan();
        $application = (new Bootstrap(resourceManagementPlan: $plan))
            ->createApplication(Environment::testing());

        self::assertSame($plan, $application->resourceManagementPlan());
        self::assertNotNull($application->resourcePathResolver());
        self::assertTrue($application->providers()->has(RuntimeResourceManagementServiceProvider::class));
    }

    public function testCapabilityIsPublishedDuringBoot(): void
    {
        $application = (new Bootstrap(resourceManagementPlan: $this->plan()))
            ->createApplication(Environment::testing());

        self::assertFalse($application->hasCapability('resource-management'));
        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->hasCapability('resource-management'));
    }

    public function testResolverUsesAuthorizedRootsFromPlan(): void
    {
        $application = (new Bootstrap(resourceManagementPlan: $this->plan()))
            ->createApplication(Environment::testing());

        $resolved = $application->resourcePathResolver()?->resolve(
            new ResourceRootIdentifier('public'),
            new ResourcePath('assets/app.js'),
        );

        self::assertNotNull($resolved);
        self::assertSame('assets/app.js', $resolved->relativePath()->value());
        self::assertFileExists($resolved->canonicalPath());
    }

    public function testConfiguredPlanIdentityIsStableAcrossLifecycle(): void
    {
        $plan = $this->plan();
        $application = (new Bootstrap(resourceManagementPlan: $plan))
            ->createApplication(Environment::testing());

        self::assertSame($plan, $application->resourceManagementPlan());
        self::assertTrue($application->boot()->succeeded());
        self::assertSame($plan, $application->resourceManagementPlan());
        self::assertTrue($application->run()->succeeded());
        self::assertSame($plan, $application->resourceManagementPlan());
        self::assertTrue($application->shutdown()->succeeded());
        self::assertSame($plan, $application->resourceManagementPlan());
    }

    public function testPlanExposesCompiledSubsystemsWithoutMutation(): void
    {
        $plan = $this->plan();

        self::assertSame(0, $plan->registry()->count());
        self::assertSame(0, $plan->contributions()->count());
        self::assertCount(1, $plan->authorizedRoots());
        self::assertSame([], $plan->translationPlans());
        self::assertNull($plan->publication());
    }

    public function testDuplicateRootIdentifiersAreRejected(): void
    {
        $root = new AuthorizedResourceRoot(new ResourceRootIdentifier('public'), $this->root);

        $this->expectException(\InvalidArgumentException::class);
        new ResourceManagementPlan(
            new CompiledResourceRegistry([]),
            new CompiledResourceContributionPlan([], []),
            [$root, $root],
        );
    }

    public function testEmptyTranslationPlanKeyIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ResourceManagementPlan(
            new CompiledResourceRegistry([]),
            new CompiledResourceContributionPlan([], []),
            [],
            ['' => new \Sif\Foundation\Resources\Localization\ImmutableTranslationPlan(
                new \Sif\Foundation\Resources\Localization\LocaleFallbackChain([
                    new \Sif\Foundation\Resources\Localization\LocaleIdentifier('en'),
                ]),
                \Sif\Foundation\Resources\ResourceNamespace::global(),
                [],
            )],
        );
    }

    private function plan(): ResourceManagementPlan
    {
        return new ResourceManagementPlan(
            new CompiledResourceRegistry([]),
            new CompiledResourceContributionPlan([], []),
            [new AuthorizedResourceRoot(new ResourceRootIdentifier('public'), $this->root)],
        );
    }
}
