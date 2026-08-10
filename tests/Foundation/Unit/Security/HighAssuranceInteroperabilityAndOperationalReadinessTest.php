<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\HighAssuranceInteroperabilityPolicyInterface;
use Sif\Foundation\Security\Contracts\VerifiableCredentialCapabilityProviderInterface;
use Sif\Foundation\Security\Contracts\VerifiableCredentialsOperationalReadinessEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\HighAssuranceInteroperabilityAssessment;
use Sif\Foundation\Security\VerifiableCredentials\HighAssuranceInteroperabilityContext;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialsOperationalReadinessReport;

final class HighAssuranceInteroperabilityAndOperationalReadinessTest extends TestCase
{
    public function testInteroperabilityContextKeepsCapabilitiesAndControlsExplicit(): void
    {
        $context = new HighAssuranceInteroperabilityContext(
            [
                'presentation-binding',
                'trust-validation',
                'selective-disclosure',
                'identity-assurance',
                'credential-status',
            ],
            [
                'replay-protection',
                'holder-binding',
                'freshness-validation',
            ]
        );

        self::assertContains(
            'presentation-binding',
            $context->availableCapabilities()
        );
        self::assertContains(
            'holder-binding',
            $context->activeControls()
        );
    }

    public function testInteroperabilityAssessmentSeparatesViolationsAndWarnings(): void
    {
        $assessment = new HighAssuranceInteroperabilityAssessment(
            false,
            ['credential_status_unavailable'],
            ['identity assurance profile requires review']
        );

        self::assertFalse($assessment->compatible());
        self::assertSame(
            ['credential_status_unavailable'],
            $assessment->violations()
        );
        self::assertSame(
            ['identity assurance profile requires review'],
            $assessment->warnings()
        );
    }

    public function testOperationalReadinessSeparatesBlockingIssuesAndAdvisories(): void
    {
        $report = new VerifiableCredentialsOperationalReadinessReport(
            false,
            ['holder binding verifier unavailable'],
            ['review issuer trust registry freshness']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['holder binding verifier unavailable'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['review issuer trust registry freshness'],
            $report->advisories()
        );
    }

    public function testInteroperabilityPolicyReturnsTypedAssessment(): void
    {
        $method = new \ReflectionMethod(
            HighAssuranceInteroperabilityPolicyInterface::class,
            'assess'
        );

        self::assertSame(
            HighAssuranceInteroperabilityAssessment::class,
            (string) $method->getReturnType()
        );
    }

    public function testReadinessEvaluatorReturnsTypedReport(): void
    {
        $method = new \ReflectionMethod(
            VerifiableCredentialsOperationalReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            VerifiableCredentialsOperationalReadinessReport::class,
            (string) $method->getReturnType()
        );
    }

    public function testCapabilityProviderRemainsGeneric(): void
    {
        self::assertTrue(
            (new \ReflectionClass(
                VerifiableCredentialCapabilityProviderInterface::class
            ))->isInterface()
        );
    }

    public function testReadinessLayerRemainsProviderAndInfrastructureNeutral(): void
    {
        foreach ([
            HighAssuranceInteroperabilityPolicyInterface::class,
            VerifiableCredentialsOperationalReadinessEvaluatorInterface::class,
            VerifiableCredentialCapabilityProviderInterface::class,
            HighAssuranceInteroperabilityContext::class,
            HighAssuranceInteroperabilityAssessment::class,
            VerifiableCredentialsOperationalReadinessReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('wallet', strtolower($source));
        }
    }
}
