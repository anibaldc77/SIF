<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialAccreditationPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialAccreditationResolverInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustRegistryMembershipPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustRegistryResolverInterface;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation\CredentialAccreditation;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation\CredentialAccreditationScope;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityRole;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Registry\CredentialTrustRegistryEntry;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Registry\CredentialTrustRegistryMembershipStatus;

final class CredentialTrustRegistryEntryAndAccreditationModelTest extends TestCase
{
    private function issuer(): CredentialTrustEntityReference
    {
        return new CredentialTrustEntityReference(
            'https://issuer.example.test',
            CredentialTrustEntityRole::Issuer,
            'ecosystem-alpha'
        );
    }

    private function authority(): CredentialTrustEntityReference
    {
        return new CredentialTrustEntityReference(
            'https://authority.example.test',
            CredentialTrustEntityRole::AccreditationAuthority,
            'ecosystem-alpha'
        );
    }

    public function testMembershipStatesAreExplicit(): void
    {
        self::assertSame('active', CredentialTrustRegistryMembershipStatus::Active->value);
        self::assertSame('suspended', CredentialTrustRegistryMembershipStatus::Suspended->value);
        self::assertSame('revoked', CredentialTrustRegistryMembershipStatus::Revoked->value);
        self::assertSame('expired', CredentialTrustRegistryMembershipStatus::Expired->value);
    }

    public function testRegistryEntryKeepsMembershipValidityAndAccreditationsExplicit(): void
    {
        $entry = new CredentialTrustRegistryEntry(
            'registry-alpha',
            $this->issuer(),
            CredentialTrustRegistryMembershipStatus::Active,
            new DateTimeImmutable('2026-01-01T00:00:00Z'),
            new DateTimeImmutable('2027-01-01T00:00:00Z'),
            ['accreditation-001'],
            ['source' => 'registry']
        );

        self::assertSame('registry-alpha', $entry->registryId());
        self::assertSame(CredentialTrustRegistryMembershipStatus::Active, $entry->membershipStatus());
        self::assertSame(['accreditation-001'], $entry->accreditationIds());
        self::assertSame('registry', $entry->metadata()['source']);
    }

    public function testAccreditationScopeKeepsCredentialTypesJurisdictionsAndConstraintsExplicit(): void
    {
        $scope = new CredentialAccreditationScope(
            'identity-credentials',
            ['IdentityCredential'],
            ['AR'],
            ['loa>=high']
        );

        self::assertSame('identity-credentials', $scope->scopeId());
        self::assertSame(['IdentityCredential'], $scope->credentialTypes());
        self::assertSame(['AR'], $scope->jurisdictions());
        self::assertSame(['loa>=high'], $scope->constraints());
    }

    public function testAccreditationKeepsSubjectAuthorityScopeAndValidityExplicit(): void
    {
        $scope = new CredentialAccreditationScope(
            'identity-credentials',
            ['IdentityCredential'],
            ['AR']
        );

        $accreditation = new CredentialAccreditation(
            'accreditation-001',
            $this->issuer(),
            $this->authority(),
            $scope,
            new DateTimeImmutable('2026-01-01T00:00:00Z'),
            new DateTimeImmutable('2026-12-31T23:59:59Z')
        );

        self::assertSame('accreditation-001', $accreditation->accreditationId());
        self::assertSame('https://issuer.example.test', $accreditation->subject()->entityId());
        self::assertSame(
            CredentialTrustEntityRole::AccreditationAuthority,
            $accreditation->authority()->role()
        );
        self::assertSame('identity-credentials', $accreditation->scope()->scopeId());
    }

    public function testRegistryAndAccreditationContractsAreSeparated(): void
    {
        foreach ([
            CredentialTrustRegistryResolverInterface::class,
            CredentialAccreditationResolverInterface::class,
            CredentialAccreditationPolicyInterface::class,
            CredentialTrustRegistryMembershipPolicyInterface::class,
        ] as $contract) {
            self::assertTrue((new \ReflectionClass($contract))->isInterface());
        }
    }

    public function testRegistryResolverReturnsTypedEntry(): void
    {
        $method = new \ReflectionMethod(
            CredentialTrustRegistryResolverInterface::class,
            'resolve'
        );

        self::assertSame(
            '?' . CredentialTrustRegistryEntry::class,
            (string) $method->getReturnType()
        );
    }

    public function testArchitecturePreservesI1TrustChainContracts(): void
    {
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialTrustChainEvaluatorInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialTrustProfilePolicyInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialTrustEntityRolePolicyInterface::class
            )
        );
    }

    public function testRegistryAndAccreditationLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            CredentialTrustRegistryResolverInterface::class,
            CredentialAccreditationResolverInterface::class,
            CredentialAccreditationPolicyInterface::class,
            CredentialTrustRegistryMembershipPolicyInterface::class,
            CredentialTrustRegistryEntry::class,
            CredentialAccreditation::class,
            CredentialAccreditationScope::class,
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
        }
    }
}
