<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Security\Contracts\OpenIdFederationRuntimeFailurePolicyInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationRuntimeFreshnessPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationRuntimeReadinessEvaluatorInterface;
use Sif\Foundation\Security\OpenIdFederation\Runtime\DefaultOpenIdFederationRuntimeFailurePolicy;
use Sif\Foundation\Security\OpenIdFederation\Runtime\DefaultOpenIdFederationRuntimeFreshnessPolicy;
use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationInteroperabilityProfile;
use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationRuntimeEvidence;
use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationRuntimeFreshnessStatus;
use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationRuntimeReadinessReport;

final class OpenIdFederationRuntimeFreshnessResilienceAndInteroperabilityTest extends TestCase
{
    private function evidence(): OpenIdFederationRuntimeEvidence
    {
        return new OpenIdFederationRuntimeEvidence(
            'https://issuer.example.test',
            new DateTimeImmutable('2026-08-13T10:00:00Z'),
            new DateTimeImmutable('2026-08-13T10:05:00Z'),
            new DateTimeImmutable('2026-08-13T10:10:00Z'),
            'statement-set-v17'
        );
    }

    public function testEvidenceKeepsEntityTimingAndVersionExplicit(): void
    {
        $evidence = $this->evidence();

        self::assertSame(
            'https://issuer.example.test',
            $evidence->entityId()
        );
        self::assertSame(
            'statement-set-v17',
            $evidence->sourceVersion()
        );
    }

    public function testFreshnessStatesAreExplicit(): void
    {
        $evidence = $this->evidence();

        self::assertSame(
            OpenIdFederationRuntimeFreshnessStatus::Fresh,
            $evidence->freshnessAt(
                new DateTimeImmutable('2026-08-13T10:04:59Z')
            )
        );
        self::assertSame(
            OpenIdFederationRuntimeFreshnessStatus::StaleUsable,
            $evidence->freshnessAt(
                new DateTimeImmutable('2026-08-13T10:07:00Z')
            )
        );
        self::assertSame(
            OpenIdFederationRuntimeFreshnessStatus::Expired,
            $evidence->freshnessAt(
                new DateTimeImmutable('2026-08-13T10:10:01Z')
            )
        );
    }

    public function testDefaultFreshnessPolicyDelegatesToEvidenceWindow(): void
    {
        $status = (new DefaultOpenIdFederationRuntimeFreshnessPolicy())->evaluate(
            $this->evidence(),
            new DateTimeImmutable('2026-08-13T10:07:00Z')
        );

        self::assertSame(
            OpenIdFederationRuntimeFreshnessStatus::StaleUsable,
            $status
        );
    }

    public function testFailurePolicyIsFailClosedByDefault(): void
    {
        $allowed = (new DefaultOpenIdFederationRuntimeFailurePolicy())
            ->mayUseCachedEvidence(
                $this->evidence(),
                new DateTimeImmutable('2026-08-13T10:07:00Z'),
                new RuntimeException('resolver unavailable')
            );

        self::assertFalse($allowed);
    }

    public function testFailurePolicyCanExplicitlyAllowUsableStale(): void
    {
        $allowed = (new DefaultOpenIdFederationRuntimeFailurePolicy(true))
            ->mayUseCachedEvidence(
                $this->evidence(),
                new DateTimeImmutable('2026-08-13T10:07:00Z'),
                new RuntimeException('resolver unavailable')
            );

        self::assertTrue($allowed);
    }

    public function testFailurePolicyNeverAllowsExpiredEvidence(): void
    {
        $allowed = (new DefaultOpenIdFederationRuntimeFailurePolicy(true))
            ->mayUseCachedEvidence(
                $this->evidence(),
                new DateTimeImmutable('2026-08-13T10:10:01Z'),
                new RuntimeException('resolver unavailable')
            );

        self::assertFalse($allowed);
    }

    public function testInteroperabilityProfileKeepsOidcAndWalletCapabilitiesExplicit(): void
    {
        $profile = new OpenIdFederationInteroperabilityProfile();

        self::assertTrue($profile->openidConnect());
        self::assertTrue($profile->openid4Vci());
        self::assertTrue($profile->openid4Vp());
        self::assertTrue($profile->walletMetadata());
        self::assertCount(4, $profile->toArray());
    }

    public function testRuntimeReadinessReportRepresentsReadyAndBlockedStates(): void
    {
        $ready = new OpenIdFederationRuntimeReadinessReport(true);

        self::assertTrue($ready->ready());

        $blocked = new OpenIdFederationRuntimeReadinessReport(
            false,
            ['trust_chain_collector_missing'],
            ['stale policy requires review']
        );

        self::assertFalse($blocked->ready());
        self::assertSame(
            ['trust_chain_collector_missing'],
            $blocked->blockingIssues()
        );
    }

    public function testRuntimeContractsAreTypedAndSeparated(): void
    {
        $freshness = new \ReflectionMethod(
            OpenIdFederationRuntimeFreshnessPolicyInterface::class,
            'evaluate'
        );
        $failure = new \ReflectionMethod(
            OpenIdFederationRuntimeFailurePolicyInterface::class,
            'mayUseCachedEvidence'
        );
        $readiness = new \ReflectionMethod(
            OpenIdFederationRuntimeReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            OpenIdFederationRuntimeFreshnessStatus::class,
            (string) $freshness->getReturnType()
        );
        self::assertSame(
            'bool',
            (string) $failure->getReturnType()
        );
        self::assertSame(
            OpenIdFederationRuntimeReadinessReport::class,
            (string) $readiness->getReturnType()
        );
    }

    public function testArchitecturePreservesI1ToI6AndWp250Boundaries(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityStatementVerifierInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationFetchProtocolInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationMetadataPolicyApplicatorInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationTrustMarkValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationTrustChainCollectorInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationCredentialTrustChainBridgeInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testRuntimeLayerRemainsTransportCryptoAndStorageNeutral(): void
    {
        foreach ([
            OpenIdFederationRuntimeFreshnessPolicyInterface::class,
            OpenIdFederationRuntimeFailurePolicyInterface::class,
            OpenIdFederationRuntimeReadinessEvaluatorInterface::class,
            OpenIdFederationRuntimeEvidence::class,
            OpenIdFederationInteroperabilityProfile::class,
            OpenIdFederationRuntimeReadinessReport::class,
            DefaultOpenIdFederationRuntimeFreshnessPolicy::class,
            DefaultOpenIdFederationRuntimeFailurePolicy::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('sleep(', strtolower($source));
        }
    }
}
