<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialStatusProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusProductCapabilities;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusProductReadinessReport;

final class CredentialStatusProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp249Surface(): void
    {
        $capabilities = new CredentialStatusProductCapabilities();

        self::assertTrue($capabilities->statusResolution());
        self::assertTrue($capabilities->revocationAndSuspension());
        self::assertTrue($capabilities->bitstringStatusList());
        self::assertTrue($capabilities->tokenStatusList());
        self::assertTrue($capabilities->issuerPublicationLifecycle());
        self::assertTrue($capabilities->verifierCaching());
        self::assertTrue($capabilities->freshnessAndRefresh());
        self::assertTrue($capabilities->resolutionFailurePolicy());
        self::assertTrue($capabilities->highAssuranceEnforcement());
        self::assertTrue($capabilities->operationalReadiness());
        self::assertCount(10, $capabilities->toArray());
    }

    public function testProductProfileMakesSecurityRequirementsExplicit(): void
    {
        $profile = new CredentialStatusProductProfile(
            'credential-status-default',
            new CredentialStatusProductCapabilities()
        );

        self::assertSame(
            'credential-status-default',
            $profile->name()
        );
        self::assertTrue($profile->requireStatusValidation());
        self::assertTrue($profile->requireFreshStatusEvidence());
        self::assertTrue($profile->requireFailClosedHighAssurance());
        self::assertTrue($profile->requireOperationalReadiness());
    }

    public function testProductReadinessReportCanRepresentReadyState(): void
    {
        $report = new CredentialStatusProductReadinessReport(true);

        self::assertTrue($report->ready());
        self::assertSame([], $report->blockingIssues());
        self::assertSame([], $report->warnings());
    }

    public function testProductReadinessReportCanRepresentBlockingState(): void
    {
        $report = new CredentialStatusProductReadinessReport(
            false,
            ['fresh_status_evidence_unavailable'],
            ['status publication refresh requires review']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['fresh_status_evidence_unavailable'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['status publication refresh requires review'],
            $report->warnings()
        );
    }

    public function testProductReadinessEvaluatorContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            CredentialStatusProductReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            CredentialStatusProductProfile::class,
            (string) $method->getParameters()[0]->getType()
        );

        self::assertSame(
            CredentialStatusProductReadinessReport::class,
            (string) $method->getReturnType()
        );
    }

    public function testCompletionPreservesSpecializedStatusContracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\HighAssuranceCredentialStatusResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialStatusProfilePolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialStatusFreshnessPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialStatusAllocationRepositoryInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialStatusPublicationPlannerInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialStatusPublisherInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialStatusCacheInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialStatusRefreshPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialStatusResolutionFailurePolicyInterface::class,
            \Sif\Foundation\Security\Contracts\TokenStatusListAuthenticityVerifierInterface::class,
            \Sif\Foundation\Security\Contracts\TokenStatusListDecoderInterface::class,
            \Sif\Foundation\Security\Contracts\TokenStatusListValuePolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                interface_exists($contract),
                $contract
            );
        }
    }

    public function testProductCompletionLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            CredentialStatusProductReadinessEvaluatorInterface::class,
            CredentialStatusProductCapabilities::class,
            CredentialStatusProductProfile::class,
            CredentialStatusProductReadinessReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString(
                'curl_',
                strtolower($source)
            );
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}