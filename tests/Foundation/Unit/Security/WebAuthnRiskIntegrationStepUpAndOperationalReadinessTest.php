<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\WebAuthnOperationalReadinessEvaluatorInterface;
use Sif\Foundation\Security\Contracts\WebAuthnRiskEvaluatorInterface;
use Sif\Foundation\Security\Contracts\WebAuthnStepUpPolicyInterface;
use Sif\Foundation\Security\WebAuthn\WebAuthnOperationalReadinessContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnOperationalReadinessReport;
use Sif\Foundation\Security\WebAuthn\WebAuthnRiskAssessment;
use Sif\Foundation\Security\WebAuthn\WebAuthnRiskContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnStepUpRequirement;

final class WebAuthnRiskIntegrationStepUpAndOperationalReadinessTest extends TestCase
{
    public function testRiskContextKeepsSignalsAndControlsExplicit(): void
    {
        $context = new WebAuthnRiskContext(
            'user-001',
            'credential-001',
            true,
            ['new-device', 'impossible-travel'],
            ['user-verification', 'attestation-trust']
        );

        self::assertSame('user-001', $context->userId());
        self::assertSame('credential-001', $context->credentialId());
        self::assertTrue($context->highRisk());
        self::assertSame(['new-device', 'impossible-travel'], $context->signals());
        self::assertContains('attestation-trust', $context->activeControls());
    }

    public function testRiskAssessmentSeparatesAcceptanceAndStepUp(): void
    {
        $assessment = new WebAuthnRiskAssessment(
            true,
            true,
            [],
            ['new_device_requires_additional_assurance']
        );

        self::assertTrue($assessment->acceptable());
        self::assertTrue($assessment->stepUpRequired());
        self::assertSame(
            ['new_device_requires_additional_assurance'],
            $assessment->warnings()
        );
    }

    public function testStepUpRequirementKeepsFactorsAndControlsExplicit(): void
    {
        $requirement = new WebAuthnStepUpRequirement(
            true,
            ['mfa'],
            ['fresh-user-verification', 'trusted-device']
        );

        self::assertTrue($requirement->required());
        self::assertSame(['mfa'], $requirement->requiredFactors());
        self::assertSame(
            ['fresh-user-verification', 'trusted-device'],
            $requirement->requiredControls()
        );
    }

    public function testOperationalReadinessKeepsCapabilitiesAndControlsExplicit(): void
    {
        $context = new WebAuthnOperationalReadinessContext(
            ['registration', 'authentication', 'attestation', 'discoverable-credentials', 'credential-lifecycle', 'recovery', 'device-migration', 'risk-integration'],
            ['challenge-replay-protection', 'origin-validation', 'rp-id-validation', 'user-verification', 'audit']
        );

        self::assertContains('risk-integration', $context->availableCapabilities());
        self::assertContains('origin-validation', $context->activeControls());

        $report = new WebAuthnOperationalReadinessReport(
            false,
            ['challenge_store_unavailable'],
            ['attestation metadata freshness requires review']
        );

        self::assertFalse($report->ready());
        self::assertSame(['challenge_store_unavailable'], $report->blockingIssues());
        self::assertSame(
            ['attestation metadata freshness requires review'],
            $report->advisories()
        );
    }

    public function testRiskStepUpAndReadinessContractsAreTyped(): void
    {
        $risk = new \ReflectionMethod(WebAuthnRiskEvaluatorInterface::class, 'evaluate');
        $stepUp = new \ReflectionMethod(WebAuthnStepUpPolicyInterface::class, 'determine');
        $readiness = new \ReflectionMethod(WebAuthnOperationalReadinessEvaluatorInterface::class, 'evaluate');

        self::assertSame(WebAuthnRiskAssessment::class, (string) $risk->getReturnType());
        self::assertSame(WebAuthnStepUpRequirement::class, (string) $stepUp->getReturnType());
        self::assertSame(
            WebAuthnOperationalReadinessReport::class,
            (string) $readiness->getReturnType()
        );
    }

    public function testRiskLayerRemainsVendorAndObservabilityNeutral(): void
    {
        foreach ([
            WebAuthnRiskEvaluatorInterface::class,
            WebAuthnStepUpPolicyInterface::class,
            WebAuthnOperationalReadinessEvaluatorInterface::class,
            WebAuthnRiskContext::class,
            WebAuthnRiskAssessment::class,
            WebAuthnStepUpRequirement::class,
            WebAuthnOperationalReadinessContext::class,
            WebAuthnOperationalReadinessReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('Splunk', $source);
            self::assertStringNotContainsString('Datadog', $source);
            self::assertStringNotContainsString('CrowdStrike', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }
}
