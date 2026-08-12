<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustOperationalReadinessEvaluatorInterface;
use Sif\Foundation\Security\Exceptions\CredentialTrustEnforcementException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustResolutionEvidence;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\CredentialTrustDecision;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Enforcement\CredentialTrustOperationalReadinessReport;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Enforcement\HighAssuranceCredentialTrustEnforcer;

final class CredentialTrustHighAssuranceEnforcementAndOperationalReadinessTest extends TestCase
{
    private function decision(
        bool $trusted = true,
        bool $fromCache = false,
        bool $stale = false,
        bool $refreshRecommended = false
    ): CredentialTrustDecision {
        return new CredentialTrustDecision(
            new CredentialTrustResolutionEvidence(
                new CredentialTrustChainAssessment(
                    $trusted,
                    [
                        'https://issuer.example.test',
                        'https://trust-anchor.example.test',
                    ]
                ),
                new DateTimeImmutable('2026-08-12T15:00:00Z'),
                'registry-version-50',
                'sha256:metadata-050'
            ),
            $fromCache,
            $stale,
            $refreshRecommended
        );
    }

    public function testHighAssuranceEnforcerAcceptsCurrentTrustedEvidence(): void
    {
        $enforcer = new HighAssuranceCredentialTrustEnforcer();

        $result = $enforcer->enforce(
            $this->decision()
        );

        self::assertTrue($result->accepted());
        self::assertTrue($result->decision()->trusted());
    }

    public function testHighAssuranceEnforcerRejectsUntrustedDecision(): void
    {
        $enforcer = new HighAssuranceCredentialTrustEnforcer();

        $this->expectException(
            CredentialTrustEnforcementException::class
        );

        $enforcer->enforce(
            $this->decision(trusted: false)
        );
    }

    public function testHighAssuranceEnforcerRejectsStaleEvidence(): void
    {
        $enforcer = new HighAssuranceCredentialTrustEnforcer();

        $this->expectException(
            CredentialTrustEnforcementException::class
        );

        $enforcer->enforce(
            $this->decision(
                fromCache: true,
                stale: true,
                refreshRecommended: true
            )
        );
    }

    public function testHighAssuranceEnforcerRejectsRefreshRecommendedDecision(): void
    {
        $enforcer = new HighAssuranceCredentialTrustEnforcer();

        $this->expectException(
            CredentialTrustEnforcementException::class
        );

        $enforcer->enforce(
            $this->decision(
                refreshRecommended: true
            )
        );
    }

    public function testOperationalReadinessReportRepresentsReadyState(): void
    {
        $report = new CredentialTrustOperationalReadinessReport(true);

        self::assertTrue($report->ready());
        self::assertSame([], $report->blockingIssues());
        self::assertSame([], $report->warnings());
    }

    public function testOperationalReadinessReportRepresentsBlockingState(): void
    {
        $report = new CredentialTrustOperationalReadinessReport(
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

    public function testEnforcementAndReadinessContractsAreTypedAndSeparated(): void
    {
        $enforce = new \ReflectionMethod(
            CredentialTrustEnforcementPolicyInterface::class,
            'enforce'
        );
        $evaluate = new \ReflectionMethod(
            CredentialTrustOperationalReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            \Sif\Foundation\Security\VerifiableCredentials\Trust\Enforcement\CredentialTrustEnforcementDecision::class,
            (string) $enforce->getReturnType()
        );
        self::assertSame(
            CredentialTrustOperationalReadinessReport::class,
            (string) $evaluate->getReturnType()
        );
    }

    public function testArchitecturePreservesI1ToI6Contracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\CredentialTrustChainEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustRegistryResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustAnchorResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustCacheInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustResolutionFailurePolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testEnforcementLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            CredentialTrustEnforcementPolicyInterface::class,
            CredentialTrustOperationalReadinessEvaluatorInterface::class,
            \Sif\Foundation\Security\VerifiableCredentials\Trust\Enforcement\CredentialTrustEnforcementDecision::class,
            CredentialTrustOperationalReadinessReport::class,
            HighAssuranceCredentialTrustEnforcer::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('jwks', strtolower($source));
            self::assertStringNotContainsString('x509', strtolower($source));
        }
    }
}
