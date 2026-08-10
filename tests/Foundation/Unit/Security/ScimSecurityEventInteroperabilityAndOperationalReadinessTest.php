<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SecurityEventProvisioningInteroperabilityPolicyInterface;
use Sif\Foundation\Security\Contracts\SecurityEventProvisioningMapperInterface;
use Sif\Foundation\Security\Contracts\SharedSignalsOperationalReadinessEvaluatorInterface;
use Sif\Foundation\Security\SharedSignals\SecurityEventInteroperabilityAssessment;
use Sif\Foundation\Security\SharedSignals\SecurityEventProvisioningContext;
use Sif\Foundation\Security\SharedSignals\SecurityEventSubject;
use Sif\Foundation\Security\SharedSignals\SharedSignalsOperationalContext;
use Sif\Foundation\Security\SharedSignals\SharedSignalsOperationalReadinessReport;

final class ScimSecurityEventInteroperabilityAndOperationalReadinessTest extends TestCase
{
    public function testProvisioningContextKeepsOperationAndResourceExplicit(): void
    {
        $context = new SecurityEventProvisioningContext(
            $this->subject(),
            'deactivate',
            'User',
            'user-001'
        );

        self::assertSame('deactivate', $context->operation());
        self::assertSame('User', $context->resourceType());
        self::assertSame('user-001', $context->resourceId());
    }

    public function testInteroperabilityAssessmentSeparatesViolationsAndWarnings(): void
    {
        $assessment = new SecurityEventInteroperabilityAssessment(
            false,
            ['subject_mapping_missing'],
            ['resource type inferred']
        );

        self::assertFalse($assessment->compatible());
        self::assertSame(
            ['subject_mapping_missing'],
            $assessment->violations()
        );
        self::assertSame(
            ['resource type inferred'],
            $assessment->warnings()
        );
    }

    public function testOperationalContextKeepsCapabilitiesAndControlsExplicit(): void
    {
        $context = new SharedSignalsOperationalContext(
            ['set-verification', 'caep', 'risc', 'delivery'],
            ['replay-protection', 'audit', 'retry-policy']
        );

        self::assertContains(
            'set-verification',
            $context->availableCapabilities()
        );
        self::assertContains('audit', $context->activeControls());
    }

    public function testReadinessReportSeparatesBlockingIssuesAndAdvisories(): void
    {
        $report = new SharedSignalsOperationalReadinessReport(
            false,
            ['replay store unavailable'],
            ['review stream retry policy']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['replay store unavailable'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['review stream retry policy'],
            $report->advisories()
        );
    }

    public function testInteroperabilityAndReadinessContractsAreTyped(): void
    {
        $policy = new \ReflectionMethod(
            SecurityEventProvisioningInteroperabilityPolicyInterface::class,
            'assess'
        );
        $readiness = new \ReflectionMethod(
            SharedSignalsOperationalReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            SecurityEventInteroperabilityAssessment::class,
            (string) $policy->getReturnType()
        );
        self::assertSame(
            SharedSignalsOperationalReadinessReport::class,
            (string) $readiness->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                SecurityEventProvisioningMapperInterface::class
            ))->isInterface()
        );
    }

    public function testInteroperabilityLayerRemainsProvisionerAndInfrastructureNeutral(): void
    {
        foreach ([
            SecurityEventProvisioningInteroperabilityPolicyInterface::class,
            SecurityEventProvisioningMapperInterface::class,
            SharedSignalsOperationalReadinessEvaluatorInterface::class,
            SecurityEventProvisioningContext::class,
            SecurityEventInteroperabilityAssessment::class,
            SharedSignalsOperationalContext::class,
            SharedSignalsOperationalReadinessReport::class,
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

    private function subject(): SecurityEventSubject
    {
        return new SecurityEventSubject(
            'account',
            [
                'iss' => 'https://issuer.example.test',
                'sub' => 'user-001',
            ]
        );
    }
}
