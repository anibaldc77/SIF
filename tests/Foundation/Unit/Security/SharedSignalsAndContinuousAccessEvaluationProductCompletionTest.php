<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SharedSignalsProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\SharedSignals\SharedSignalsProductCapabilities;
use Sif\Foundation\Security\SharedSignals\SharedSignalsProductProfile;
use Sif\Foundation\Security\SharedSignals\SharedSignalsProductReadinessReport;

final class SharedSignalsAndContinuousAccessEvaluationProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp243Surface(): void
    {
        $capabilities = new SharedSignalsProductCapabilities();

        self::assertTrue($capabilities->securityEventTokens());
        self::assertTrue($capabilities->subjectIdentifiers());
        self::assertTrue($capabilities->streamDelivery());
        self::assertTrue($capabilities->caep());
        self::assertTrue($capabilities->risc());
        self::assertTrue($capabilities->continuousAccessReactions());
        self::assertTrue($capabilities->provisioningInteroperability());
        self::assertCount(7, $capabilities->toArray());
    }

    public function testProductProfileMakesReadinessRequirementsExplicit(): void
    {
        $profile = new SharedSignalsProductProfile(
            'shared-signals-default',
            new SharedSignalsProductCapabilities()
        );

        self::assertSame('shared-signals-default', $profile->name());
        self::assertTrue($profile->requireReplayProtection());
        self::assertTrue($profile->requireOperationalReadiness());
        self::assertTrue($profile->requireContinuousAccessReaction());
    }

    public function testReadinessReportCanRepresentReadyState(): void
    {
        $report = new SharedSignalsProductReadinessReport(true);

        self::assertTrue($report->ready());
        self::assertSame([], $report->blockingIssues());
        self::assertSame([], $report->warnings());
    }

    public function testReadinessReportCanRepresentBlockingState(): void
    {
        $report = new SharedSignalsProductReadinessReport(
            false,
            ['replay protection unavailable'],
            ['review delivery retry policy']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['replay protection unavailable'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['review delivery retry policy'],
            $report->warnings()
        );
    }

    public function testReadinessEvaluatorContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            SharedSignalsProductReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            SharedSignalsProductReadinessReport::class,
            (string) $method->getReturnType()
        );

        self::assertSame(
            SharedSignalsProductProfile::class,
            (string) $method->getParameters()[0]->getType()
        );
    }

    public function testProductCompletionLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            SharedSignalsProductCapabilities::class,
            SharedSignalsProductProfile::class,
            SharedSignalsProductReadinessReport::class,
            SharedSignalsProductReadinessEvaluatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
