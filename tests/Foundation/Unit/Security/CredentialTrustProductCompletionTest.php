<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialTrustProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Product\CredentialTrustProductCapabilities;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Product\CredentialTrustProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Product\CredentialTrustProductReadinessReport;

final class CredentialTrustProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp250Surface(): void
    {
        $capabilities = new CredentialTrustProductCapabilities();

        self::assertTrue($capabilities->trustArchitecture());
        self::assertTrue($capabilities->registryAndAccreditation());
        self::assertTrue($capabilities->trustAnchorsAndKeyLifecycle());
        self::assertTrue($capabilities->trustChainResolutionAndValidation());
        self::assertTrue($capabilities->cachingFreshnessAndMetadataConsistency());
        self::assertTrue($capabilities->failurePolicyAndResilience());
        self::assertTrue($capabilities->highAssuranceEnforcement());
        self::assertTrue($capabilities->operationalReadiness());
        self::assertCount(8, $capabilities->toArray());
    }

    public function testProductProfileMakesSecurityRequirementsExplicit(): void
    {
        $profile = new CredentialTrustProductProfile(
            'credential-trust-default',
            new CredentialTrustProductCapabilities()
        );

        self::assertSame('credential-trust-default', $profile->name());
        self::assertTrue($profile->requireValidatedTrustChain());
        self::assertTrue($profile->requireCurrentTrustEvidence());
        self::assertTrue($profile->requireFailClosedHighAssurance());
        self::assertTrue($profile->requireOperationalReadiness());
    }

    public function testProductReadinessReportRepresentsReadyState(): void
    {
        $report = new CredentialTrustProductReadinessReport(true);

        self::assertTrue($report->ready());
        self::assertSame([], $report->blockingIssues());
        self::assertSame([], $report->warnings());
    }

    public function testProductReadinessReportRepresentsBlockingState(): void
    {
        $report = new CredentialTrustProductReadinessReport(
            false,
            ['trust_anchor_configuration_missing'],
            ['metadata refresh interval requires review']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['trust_anchor_configuration_missing'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['metadata refresh interval requires review'],
            $report->warnings()
        );
    }

    public function testProductReadinessEvaluatorContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            CredentialTrustProductReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            CredentialTrustProductProfile::class,
            (string) $method->getParameters()[0]->getType()
        );
        self::assertSame(
            CredentialTrustProductReadinessReport::class,
            (string) $method->getReturnType()
        );
    }

    public function testCompletionPreservesSpecializedTrustContracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\CredentialTrustChainEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustRegistryResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialAccreditationResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustAnchorResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustKeyMaterialResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustCacheInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustRefreshPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustMetadataConsistencyPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustResolutionFailurePolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustOperationalReadinessEvaluatorInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testProductCompletionLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            CredentialTrustProductReadinessEvaluatorInterface::class,
            CredentialTrustProductCapabilities::class,
            CredentialTrustProductProfile::class,
            CredentialTrustProductReadinessReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Memcached', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('jwks', strtolower($source));
            self::assertStringNotContainsString('x509', strtolower($source));
        }
    }
}
