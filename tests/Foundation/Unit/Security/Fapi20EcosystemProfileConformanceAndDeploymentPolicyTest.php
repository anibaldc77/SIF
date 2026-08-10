<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FapiDeploymentConformanceEvaluatorInterface;
use Sif\Foundation\Security\Contracts\FapiDeploymentProfileProviderInterface;
use Sif\Foundation\Security\Contracts\FapiDeploymentReadinessEvaluatorInterface;
use Sif\Foundation\Security\Fapi\FapiDeploymentConformanceAssessment;
use Sif\Foundation\Security\Fapi\FapiDeploymentContext;
use Sif\Foundation\Security\Fapi\FapiDeploymentProfile;
use Sif\Foundation\Security\Fapi\FapiDeploymentReadinessReport;

final class Fapi20EcosystemProfileConformanceAndDeploymentPolicyTest extends TestCase
{
    public function testDeploymentProfileDescribesRequiredCapabilitiesAndControls(): void
    {
        $profile = new FapiDeploymentProfile(
            'financial-grade',
            '2.0',
            ['par', 'pkce', 'jarm', 'sender-constrained-tokens'],
            ['issuer-validation', 'audience-validation', 'replay-protection']
        );

        self::assertSame('financial-grade', $profile->name());
        self::assertSame('2.0', $profile->version());
        self::assertContains('jarm', $profile->requiredCapabilities());
        self::assertContains(
            'replay-protection',
            $profile->requiredSecurityControls()
        );
    }

    public function testDeploymentContextKeepsRuntimeEvidenceExplicit(): void
    {
        $context = new FapiDeploymentContext(
            ['par', 'pkce', 'jarm'],
            ['issuer-validation', 'audience-validation']
        );

        self::assertContains('par', $context->availableCapabilities());
        self::assertContains(
            'issuer-validation',
            $context->activeSecurityControls()
        );
    }

    public function testConformanceAssessmentSeparatesMissingEvidence(): void
    {
        $assessment = new FapiDeploymentConformanceAssessment(
            false,
            ['sender-constrained-tokens'],
            ['replay-protection'],
            ['metadata should be reviewed']
        );

        self::assertFalse($assessment->conformant());
        self::assertSame(
            ['sender-constrained-tokens'],
            $assessment->missingCapabilities()
        );
        self::assertSame(
            ['replay-protection'],
            $assessment->missingSecurityControls()
        );
        self::assertSame(
            ['metadata should be reviewed'],
            $assessment->warnings()
        );
    }

    public function testReadinessReportSeparatesBlockingIssuesAndAdvisories(): void
    {
        $report = new FapiDeploymentReadinessReport(
            false,
            ['jarm unavailable'],
            ['review certification evidence']
        );

        self::assertFalse($report->ready());
        self::assertSame(['jarm unavailable'], $report->blockingIssues());
        self::assertSame(
            ['review certification evidence'],
            $report->advisories()
        );
    }

    public function testDeploymentContractsExposeTypedBoundaries(): void
    {
        $provider = new \ReflectionMethod(
            FapiDeploymentProfileProviderInterface::class,
            'profile'
        );
        $conformance = new \ReflectionMethod(
            FapiDeploymentConformanceEvaluatorInterface::class,
            'evaluate'
        );
        $readiness = new \ReflectionMethod(
            FapiDeploymentReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            FapiDeploymentProfile::class,
            (string) $provider->getReturnType()
        );
        self::assertSame(
            FapiDeploymentConformanceAssessment::class,
            (string) $conformance->getReturnType()
        );
        self::assertSame(
            FapiDeploymentReadinessReport::class,
            (string) $readiness->getReturnType()
        );
    }

    public function testDeploymentPolicyLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            FapiDeploymentProfile::class,
            FapiDeploymentContext::class,
            FapiDeploymentConformanceAssessment::class,
            FapiDeploymentReadinessReport::class,
            FapiDeploymentProfileProviderInterface::class,
            FapiDeploymentConformanceEvaluatorInterface::class,
            FapiDeploymentReadinessEvaluatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
        }
    }
}
