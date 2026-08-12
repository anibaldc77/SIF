<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Security\Exceptions\CredentialTrustResolutionUnavailableException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustCacheEntry;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustResolutionEvidence;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\CredentialTrustResolutionFailureMode;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\DefaultCredentialTrustResolutionFailurePolicy;

final class CredentialTrustDecisionFailurePolicyAndResilienceTest extends TestCase
{
    private function cacheEntry(): CredentialTrustCacheEntry
    {
        return new CredentialTrustCacheEntry(
            'trust:https://issuer.example.test',
            new CredentialTrustResolutionEvidence(
                new CredentialTrustChainAssessment(
                    true,
                    [
                        'https://issuer.example.test',
                        'https://trust-anchor.example.test',
                    ]
                ),
                new DateTimeImmutable('2026-08-12T14:00:00Z'),
                'registry-version-42',
                'sha256:metadata-001'
            ),
            new DateTimeImmutable('2026-08-12T14:00:00Z'),
            new DateTimeImmutable('2026-08-12T14:05:00Z'),
            new DateTimeImmutable('2026-08-12T14:10:00Z')
        );
    }

    public function testFailClosedIsTheDefaultMode(): void
    {
        $policy = new DefaultCredentialTrustResolutionFailurePolicy();

        $this->expectException(
            CredentialTrustResolutionUnavailableException::class
        );

        $policy->decide(
            $this->cacheEntry(),
            new DateTimeImmutable('2026-08-12T14:07:00Z'),
            new RuntimeException('Trust resolver unavailable.')
        );
    }

    public function testAllowUsableStaleReturnsCachedTrustEvidenceWithinStaleWindow(): void
    {
        $policy = new DefaultCredentialTrustResolutionFailurePolicy(
            CredentialTrustResolutionFailureMode::AllowUsableStale
        );

        $decision = $policy->decide(
            $this->cacheEntry(),
            new DateTimeImmutable('2026-08-12T14:07:00Z'),
            new RuntimeException('Trust resolver unavailable.')
        );

        self::assertTrue($decision->trusted());
        self::assertTrue($decision->fromCache());
        self::assertTrue($decision->stale());
        self::assertTrue($decision->refreshRecommended());
        self::assertSame(
            'registry-version-42',
            $decision->evidence()->sourceVersion()
        );
    }

    public function testAllowUsableStaleRejectsExpiredEvidence(): void
    {
        $policy = new DefaultCredentialTrustResolutionFailurePolicy(
            CredentialTrustResolutionFailureMode::AllowUsableStale
        );

        $this->expectException(
            CredentialTrustResolutionUnavailableException::class
        );

        $policy->decide(
            $this->cacheEntry(),
            new DateTimeImmutable('2026-08-12T14:10:01Z'),
            new RuntimeException('Trust resolver unavailable.')
        );
    }

    public function testAllowUsableStaleRejectsMissingCache(): void
    {
        $policy = new DefaultCredentialTrustResolutionFailurePolicy(
            CredentialTrustResolutionFailureMode::AllowUsableStale
        );

        $this->expectException(
            CredentialTrustResolutionUnavailableException::class
        );

        $policy->decide(
            null,
            new DateTimeImmutable('2026-08-12T14:07:00Z'),
            new RuntimeException('Trust resolver unavailable.')
        );
    }

    public function testResolutionFailurePreservesOriginalCause(): void
    {
        $policy = new DefaultCredentialTrustResolutionFailurePolicy();
        $failure = new RuntimeException('Network failure.');

        try {
            $policy->decide(
                null,
                new DateTimeImmutable('2026-08-12T14:07:00Z'),
                $failure
            );

            self::fail('Expected credential trust resolution failure.');
        } catch (CredentialTrustResolutionUnavailableException $exception) {
            self::assertSame($failure, $exception->getPrevious());
        }
    }

    public function testFailureModesAreExplicit(): void
    {
        self::assertSame(
            'fail_closed',
            CredentialTrustResolutionFailureMode::FailClosed->value
        );
        self::assertSame(
            'allow_usable_stale',
            CredentialTrustResolutionFailureMode::AllowUsableStale->value
        );
    }

    public function testFailurePolicyContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            \Sif\Foundation\Security\Contracts\CredentialTrustResolutionFailurePolicyInterface::class,
            'decide'
        );

        self::assertSame(
            \Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\CredentialTrustDecision::class,
            (string) $method->getReturnType()
        );
    }

    public function testArchitecturePreservesI1ToI5Contracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\CredentialTrustChainEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustRegistryResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustAnchorResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustCacheInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustRefreshPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustMetadataConsistencyPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testFailurePolicyLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\CredentialTrustResolutionFailurePolicyInterface::class,
            \Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\CredentialTrustDecision::class,
            DefaultCredentialTrustResolutionFailurePolicy::class,
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
            self::assertStringNotContainsString('sleep(', strtolower($source));
        }
    }
}
