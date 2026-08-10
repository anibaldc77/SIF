<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\VerifiableCredentialsProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialsProductCapabilities;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialsProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialsProductReadinessReport;

final class VerifiableCredentialsAndHighAssuranceIdentityPresentationProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp244Surface(): void
    {
        $capabilities = new VerifiableCredentialsProductCapabilities();

        self::assertTrue($capabilities->presentationBinding());
        self::assertTrue($capabilities->credentialFormatExtensibility());
        self::assertTrue($capabilities->trustValidation());
        self::assertTrue($capabilities->selectiveDisclosure());
        self::assertTrue($capabilities->holderBinding());
        self::assertTrue($capabilities->identityAssurance());
        self::assertTrue($capabilities->credentialStatus());
        self::assertTrue($capabilities->operationalReadiness());
        self::assertCount(8, $capabilities->toArray());
    }

    public function testProductProfileMakesSecurityRequirementsExplicit(): void
    {
        $profile = new VerifiableCredentialsProductProfile(
            'vc-high-assurance-default',
            new VerifiableCredentialsProductCapabilities()
        );

        self::assertSame('vc-high-assurance-default', $profile->name());
        self::assertTrue($profile->requireReplayProtection());
        self::assertTrue($profile->requireHolderBinding());
        self::assertTrue($profile->requireCredentialStatus());
        self::assertTrue($profile->requireIdentityAssurance());
    }

    public function testReadinessReportCanRepresentReadyState(): void
    {
        $report = new VerifiableCredentialsProductReadinessReport(true);

        self::assertTrue($report->ready());
        self::assertSame([], $report->blockingIssues());
        self::assertSame([], $report->warnings());
    }

    public function testReadinessReportCanRepresentBlockingState(): void
    {
        $report = new VerifiableCredentialsProductReadinessReport(
            false,
            ['holder_binding_unavailable'],
            ['external certification not evaluated']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['holder_binding_unavailable'],
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
            VerifiableCredentialsProductReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            VerifiableCredentialsProductReadinessReport::class,
            (string) $method->getReturnType()
        );

        self::assertSame(
            VerifiableCredentialsProductProfile::class,
            (string) $method->getParameters()[0]->getType()
        );
    }

    public function testProductCompletionLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            VerifiableCredentialsProductCapabilities::class,
            VerifiableCredentialsProductProfile::class,
            VerifiableCredentialsProductReadinessReport::class,
            VerifiableCredentialsProductReadinessEvaluatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
            self::assertStringNotContainsString('wallet', strtolower($source));
        }
    }
}
