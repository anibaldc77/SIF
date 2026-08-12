<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialTrustCacheInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustMetadataConsistencyPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustMetadataSnapshotResolverInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustRefreshPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustCacheEntry;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustResolutionEvidence;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Metadata\CredentialTrustMetadataConsistencyResult;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Metadata\CredentialTrustMetadataSnapshot;

final class CredentialTrustResolutionCachingFreshnessAndMetadataConsistencyTest extends TestCase
{
    private function evidence(): CredentialTrustResolutionEvidence
    {
        return new CredentialTrustResolutionEvidence(
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
        );
    }

    public function testResolutionEvidenceKeepsAssessmentTimeVersionAndFingerprintExplicit(): void
    {
        $evidence = $this->evidence();

        self::assertTrue($evidence->assessment()->trusted());
        self::assertSame(
            'registry-version-42',
            $evidence->sourceVersion()
        );
        self::assertSame(
            'sha256:metadata-001',
            $evidence->metadataFingerprint()
        );
    }

    public function testCacheEntryDistinguishesFreshAndUsableStaleWindows(): void
    {
        $entry = new CredentialTrustCacheEntry(
            'trust:https://issuer.example.test',
            $this->evidence(),
            new DateTimeImmutable('2026-08-12T14:00:00Z'),
            new DateTimeImmutable('2026-08-12T14:05:00Z'),
            new DateTimeImmutable('2026-08-12T14:10:00Z')
        );

        self::assertTrue(
            $entry->isFreshAt(
                new DateTimeImmutable('2026-08-12T14:04:59Z')
            )
        );
        self::assertFalse(
            $entry->isUsableStaleAt(
                new DateTimeImmutable('2026-08-12T14:04:59Z')
            )
        );
        self::assertTrue(
            $entry->isUsableStaleAt(
                new DateTimeImmutable('2026-08-12T14:07:00Z')
            )
        );
        self::assertFalse(
            $entry->isUsableStaleAt(
                new DateTimeImmutable('2026-08-12T14:10:01Z')
            )
        );
    }

    public function testMetadataSnapshotKeepsVersionFingerprintValidityAndAttributesExplicit(): void
    {
        $snapshot = new CredentialTrustMetadataSnapshot(
            'https://issuer.example.test',
            'metadata-v7',
            'sha256:abc123',
            new DateTimeImmutable('2026-08-12T14:00:00Z'),
            new DateTimeImmutable('2026-08-12T15:00:00Z'),
            ['trust_framework' => 'ecosystem-alpha']
        );

        self::assertSame(
            'https://issuer.example.test',
            $snapshot->entityId()
        );
        self::assertSame('metadata-v7', $snapshot->version());
        self::assertSame('sha256:abc123', $snapshot->fingerprint());
        self::assertSame(
            'ecosystem-alpha',
            $snapshot->attributes()['trust_framework']
        );
    }

    public function testConsistencyResultRepresentsConsistentAndBlockedStates(): void
    {
        $consistent = new CredentialTrustMetadataConsistencyResult(true);

        self::assertTrue($consistent->consistent());
        self::assertSame([], $consistent->violations());

        $blocked = new CredentialTrustMetadataConsistencyResult(
            false,
            ['issuer_identity_changed'],
            ['metadata key rotation detected']
        );

        self::assertFalse($blocked->consistent());
        self::assertSame(
            ['issuer_identity_changed'],
            $blocked->violations()
        );
        self::assertSame(
            ['metadata key rotation detected'],
            $blocked->warnings()
        );
    }

    public function testCachingFreshnessAndConsistencyContractsAreSeparated(): void
    {
        foreach ([
            CredentialTrustCacheInterface::class,
            CredentialTrustRefreshPolicyInterface::class,
            CredentialTrustMetadataSnapshotResolverInterface::class,
            CredentialTrustMetadataConsistencyPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testMetadataConsistencyContractReturnsTypedResult(): void
    {
        $method = new \ReflectionMethod(
            CredentialTrustMetadataConsistencyPolicyInterface::class,
            'compare'
        );

        self::assertSame(
            CredentialTrustMetadataConsistencyResult::class,
            (string) $method->getReturnType()
        );
    }

    public function testArchitecturePreservesI1ToI4Contracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\CredentialTrustChainEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustRegistryResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustAnchorResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustKeyMaterialResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainValidationPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testTrustCachingLayerRemainsInfrastructureAndTransportNeutral(): void
    {
        foreach ([
            CredentialTrustCacheInterface::class,
            CredentialTrustRefreshPolicyInterface::class,
            CredentialTrustMetadataSnapshotResolverInterface::class,
            CredentialTrustMetadataConsistencyPolicyInterface::class,
            CredentialTrustCacheEntry::class,
            CredentialTrustResolutionEvidence::class,
            CredentialTrustMetadataSnapshot::class,
            CredentialTrustMetadataConsistencyResult::class,
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
            self::assertStringNotContainsString('file_put_contents', strtolower($source));
        }
    }
}
