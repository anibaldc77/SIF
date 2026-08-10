<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FapiProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\Fapi\FapiProductCapabilities;
use Sif\Foundation\Security\Fapi\FapiProductProfile;
use Sif\Foundation\Security\Fapi\FapiProductReadinessReport;

final class Fapi20ProductCompletionAndSecurityReadinessTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp242Surface(): void
    {
        $capabilities = new FapiProductCapabilities();

        self::assertTrue($capabilities->clientProfile());
        self::assertTrue($capabilities->authorizationServerProfile());
        self::assertTrue($capabilities->parPkceIssuerMetadataConformance());
        self::assertTrue($capabilities->senderConstrainedTokens());
        self::assertTrue($capabilities->resourceServerEnforcement());
        self::assertTrue($capabilities->messageSigning());
        self::assertTrue($capabilities->deploymentConformance());
        self::assertCount(7, $capabilities->toArray());
    }

    public function testProductProfileMakesSecurityReadinessExplicit(): void
    {
        $profile = new FapiProductProfile(
            'fapi-2-default',
            new FapiProductCapabilities()
        );

        self::assertSame('fapi-2-default', $profile->name());
        self::assertTrue($profile->requireStrictConformance());
        self::assertTrue($profile->requireDeploymentReadiness());
        self::assertTrue($profile->requireMessageSigning());
    }

    public function testReadinessReportCanRepresentReadyState(): void
    {
        $report = new FapiProductReadinessReport(true);

        self::assertTrue($report->ready());
        self::assertSame([], $report->blockingIssues());
        self::assertSame([], $report->warnings());
    }

    public function testReadinessReportCanRepresentBlockingState(): void
    {
        $report = new FapiProductReadinessReport(
            false,
            ['sender_constraint_missing'],
            ['external certification not evaluated']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['sender_constraint_missing'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['external certification not evaluated'],
            $report->warnings()
        );
    }

    public function testReadinessEvaluatorContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            FapiProductReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            FapiProductReadinessReport::class,
            (string) $method->getReturnType()
        );

        self::assertSame(
            FapiProductProfile::class,
            (string) $method->getParameters()[0]->getType()
        );
    }

    public function testProductCompletionLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            FapiProductCapabilities::class,
            FapiProductProfile::class,
            FapiProductReadinessReport::class,
            FapiProductReadinessEvaluatorInterface::class,
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
