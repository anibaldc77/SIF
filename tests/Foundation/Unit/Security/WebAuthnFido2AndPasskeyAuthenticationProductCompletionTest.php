<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\WebAuthnProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\WebAuthn\WebAuthnProductCapabilities;
use Sif\Foundation\Security\WebAuthn\WebAuthnProductProfile;
use Sif\Foundation\Security\WebAuthn\WebAuthnProductReadinessReport;

final class WebAuthnFido2AndPasskeyAuthenticationProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp246Surface(): void
    {
        $capabilities = new WebAuthnProductCapabilities();

        self::assertTrue($capabilities->registration());
        self::assertTrue($capabilities->authentication());
        self::assertTrue($capabilities->attestationTrust());
        self::assertTrue($capabilities->discoverableCredentials());
        self::assertTrue($capabilities->passkeyUxPolicies());
        self::assertTrue($capabilities->credentialLifecycle());
        self::assertTrue($capabilities->recovery());
        self::assertTrue($capabilities->deviceMigration());
        self::assertTrue($capabilities->riskIntegration());
        self::assertTrue($capabilities->operationalReadiness());
        self::assertCount(10, $capabilities->toArray());
    }

    public function testProductProfileMakesSecurityRequirementsExplicit(): void
    {
        $profile = new WebAuthnProductProfile(
            'webauthn-default',
            new WebAuthnProductCapabilities()
        );

        self::assertSame('webauthn-default', $profile->name());
        self::assertTrue($profile->requireUserVerification());
        self::assertTrue($profile->requireChallengeReplayProtection());
        self::assertTrue($profile->requireOriginValidation());
        self::assertTrue($profile->requireRpIdValidation());
        self::assertTrue($profile->requireOperationalReadiness());
    }

    public function testReadinessReportCanRepresentReadyState(): void
    {
        $report = new WebAuthnProductReadinessReport(true);

        self::assertTrue($report->ready());
        self::assertSame([], $report->blockingIssues());
        self::assertSame([], $report->warnings());
    }

    public function testReadinessReportCanRepresentBlockingState(): void
    {
        $report = new WebAuthnProductReadinessReport(
            false,
            ['challenge_store_unavailable'],
            ['attestation metadata freshness requires review']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['challenge_store_unavailable'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['attestation metadata freshness requires review'],
            $report->warnings()
        );
    }

    public function testReadinessEvaluatorContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            WebAuthnProductReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            WebAuthnProductReadinessReport::class,
            (string) $method->getReturnType()
        );
        self::assertSame(
            WebAuthnProductProfile::class,
            (string) $method->getParameters()[0]->getType()
        );
    }

    public function testProductCompletionLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            WebAuthnProductCapabilities::class,
            WebAuthnProductProfile::class,
            WebAuthnProductReadinessReport::class,
            WebAuthnProductReadinessEvaluatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('navigator.credentials', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
