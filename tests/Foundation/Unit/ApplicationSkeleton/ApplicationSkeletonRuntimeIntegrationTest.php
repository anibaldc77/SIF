<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ApplicationSkeleton;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Runtime\ApplicationSkeletonRuntime;
use Sif\Foundation\ApplicationSkeleton\Runtime\ApplicationSkeletonRuntimeServiceProvider;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Environment;

final class ApplicationSkeletonRuntimeIntegrationTest extends TestCase
{
    public function testRuntimeExposesSafeCompositionSummary(): void
    {
        $runtime = new ApplicationSkeletonRuntime();

        self::assertSame([
            'template_factory' => true,
            'code_template_factory' => true,
            'validator_factory' => true,
            'example_factory' => true,
        ], $runtime->summary());
    }

    public function testServiceProviderPublishesCapabilitiesWithoutExecutingGeneration(): void
    {
        $provider = new ApplicationSkeletonRuntimeServiceProvider(new ApplicationSkeletonRuntime());
        $capabilities = [];

        foreach ($provider->capabilities() as $capability) {
            $capabilities[] = $capability->identifier();
        }

        self::assertSame([
            'application-skeleton',
            'application-skeleton.first-run',
        ], $capabilities);
    }

    public function testBootstrapPublishesOptionalRuntimeOnApplication(): void
    {
        $runtime = new ApplicationSkeletonRuntime();
        $application = (new Bootstrap(applicationSkeleton: $runtime))
            ->createApplication(Environment::testing());

        self::assertSame($runtime, $application->applicationSkeleton());
    }
}
