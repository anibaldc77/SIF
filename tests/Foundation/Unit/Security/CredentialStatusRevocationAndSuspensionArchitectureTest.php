<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialStatusFreshnessPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialStatusProfilePolicyInterface;
use Sif\Foundation\Security\Contracts\HighAssuranceCredentialStatusResolverInterface;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusMechanism;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusProfile;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusPurpose;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusReference;

final class CredentialStatusRevocationAndSuspensionArchitectureTest extends TestCase
{
    public function testStatusPurposesAreExplicit(): void
    {
        self::assertSame(
            'revocation',
            CredentialStatusPurpose::Revocation->value
        );
        self::assertSame(
            'suspension',
            CredentialStatusPurpose::Suspension->value
        );
    }

    public function testSupportedStatusMechanismsAreExplicit(): void
    {
        self::assertSame(
            'bitstring_status_list',
            CredentialStatusMechanism::BitstringStatusList->value
        );
        self::assertSame(
            'token_status_list',
            CredentialStatusMechanism::TokenStatusList->value
        );
    }

    public function testProfileKeepsMechanismVersionPurposesAndOptionsExplicit(): void
    {
        $profile = new CredentialStatusProfile(
            CredentialStatusMechanism::BitstringStatusList,
            '1.0',
            [
                CredentialStatusPurpose::Revocation,
                CredentialStatusPurpose::Suspension,
            ],
            ['minimum_entries' => 131072]
        );

        self::assertSame(
            CredentialStatusMechanism::BitstringStatusList,
            $profile->mechanism()
        );
        self::assertSame('1.0', $profile->profileVersion());
        self::assertCount(2, $profile->supportedPurposes());
        self::assertSame(
            131072,
            $profile->options()['minimum_entries']
        );
    }

    public function testStatusReferenceKeepsCredentialListIndexAndPurposeExplicit(): void
    {
        $reference = new CredentialStatusReference(
            'credential-001',
            'https://issuer.example.test/status/1',
            42,
            CredentialStatusPurpose::Revocation
        );

        self::assertSame('credential-001', $reference->credentialId());
        self::assertSame(
            'https://issuer.example.test/status/1',
            $reference->statusListUri()
        );
        self::assertSame(42, $reference->index());
        self::assertSame(
            CredentialStatusPurpose::Revocation,
            $reference->purpose()
        );
    }

    public function testAssessmentSeparatesStatusAndFreshnessMetadata(): void
    {
        $evaluatedAt = new DateTimeImmutable(
            '2026-08-11T12:00:00Z'
        );
        $updatedAt = new DateTimeImmutable(
            '2026-08-11T11:55:00Z'
        );
        $assessment = new CredentialStatusAssessment(
            true,
            false,
            false,
            $evaluatedAt,
            $updatedAt
        );

        self::assertTrue($assessment->valid());
        self::assertFalse($assessment->revoked());
        self::assertFalse($assessment->suspended());
        self::assertSame($evaluatedAt, $assessment->evaluatedAt());
        self::assertSame($updatedAt, $assessment->sourceUpdatedAt());
    }

    public function testStatusContractsAreTypedAndSeparated(): void
    {
        $resolver = new \ReflectionMethod(
            HighAssuranceCredentialStatusResolverInterface::class,
            'resolve'
        );

        self::assertSame(
            CredentialStatusAssessment::class,
            (string) $resolver->getReturnType()
        );

        foreach ([
            CredentialStatusProfilePolicyInterface::class,
            CredentialStatusFreshnessPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testStatusLayerRemainsTransportCompressionAndStorageNeutral(): void
    {
        foreach ([
            HighAssuranceCredentialStatusResolverInterface::class,
            CredentialStatusProfilePolicyInterface::class,
            CredentialStatusFreshnessPolicyInterface::class,
            CredentialStatusProfile::class,
            CredentialStatusReference::class,
            CredentialStatusAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('gzdecode', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Firebase', $source);
        }
    }
}

