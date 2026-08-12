<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialTrustChainEvaluatorInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustEntityRolePolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustProfilePolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainContext;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityRole;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustModel;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;

final class CredentialTrustRegistryAccreditationAndTrustChainArchitectureTest extends TestCase
{
    public function testTrustModelsAreExplicit(): void
    {
        self::assertSame('direct', CredentialTrustModel::Direct->value);
        self::assertSame('registry', CredentialTrustModel::Registry->value);
        self::assertSame('federation', CredentialTrustModel::Federation->value);
        self::assertSame('pki', CredentialTrustModel::Pki->value);
    }

    public function testTrustEntityRolesAreExplicit(): void
    {
        self::assertSame('issuer', CredentialTrustEntityRole::Issuer->value);
        self::assertSame('verifier', CredentialTrustEntityRole::Verifier->value);
        self::assertSame('wallet', CredentialTrustEntityRole::Wallet->value);
        self::assertSame(
            'trust_anchor',
            CredentialTrustEntityRole::TrustAnchor->value
        );
        self::assertSame(
            'accreditation_authority',
            CredentialTrustEntityRole::AccreditationAuthority->value
        );
    }

    public function testProfileKeepsVersionModelsRolesAndOptionsExplicit(): void
    {
        $profile = new CredentialTrustProfile(
            'credential-trust-default',
            '1.0',
            [
                CredentialTrustModel::Registry,
                CredentialTrustModel::Federation,
            ],
            [
                CredentialTrustEntityRole::Issuer,
                CredentialTrustEntityRole::TrustAnchor,
            ],
            ['require_accreditation' => true]
        );

        self::assertSame('credential-trust-default', $profile->name());
        self::assertSame('1.0', $profile->profileVersion());
        self::assertCount(2, $profile->allowedModels());
        self::assertCount(2, $profile->requiredRoles());
        self::assertTrue($profile->options()['require_accreditation']);
    }

    public function testEntityReferenceKeepsRoleAndFrameworkExplicit(): void
    {
        $entity = new CredentialTrustEntityReference(
            'https://issuer.example.test',
            CredentialTrustEntityRole::Issuer,
            'ecosystem-alpha'
        );

        self::assertSame(
            'https://issuer.example.test',
            $entity->entityId()
        );
        self::assertSame(
            CredentialTrustEntityRole::Issuer,
            $entity->role()
        );
        self::assertSame(
            'ecosystem-alpha',
            $entity->trustFrameworkId()
        );
    }

    public function testTrustChainContextKeepsAnchorsAndMaximumDepthExplicit(): void
    {
        $at = new DateTimeImmutable('2026-08-11T16:30:00Z');
        $context = new CredentialTrustChainContext(
            $at,
            ['https://trust-anchor.example.test'],
            6
        );

        self::assertSame($at, $context->evaluatedAt());
        self::assertSame(
            ['https://trust-anchor.example.test'],
            $context->trustedAnchorIds()
        );
        self::assertSame(6, $context->maximumDepth());
    }

    public function testTrustChainAssessmentKeepsPathViolationsAndWarningsExplicit(): void
    {
        $assessment = new CredentialTrustChainAssessment(
            true,
            [
                'https://issuer.example.test',
                'https://authority.example.test',
                'https://trust-anchor.example.test',
            ],
            [],
            ['metadata freshness policy requires review']
        );

        self::assertTrue($assessment->trusted());
        self::assertCount(3, $assessment->pathEntityIds());
        self::assertSame([], $assessment->violations());
        self::assertSame(
            ['metadata freshness policy requires review'],
            $assessment->warnings()
        );
    }

    public function testTrustContractsAreTypedAndSeparated(): void
    {
        $method = new \ReflectionMethod(
            CredentialTrustChainEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            CredentialTrustChainAssessment::class,
            (string) $method->getReturnType()
        );

        foreach ([
            CredentialTrustProfilePolicyInterface::class,
            CredentialTrustEntityRolePolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testArchitecturePreservesExistingCredentialTrustContracts(): void
    {
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialTrustPolicyInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialIssuerTrustResolverInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialIssuerMetadataResolverInterface::class
            )
        );
    }

    public function testTrustArchitectureRemainsRegistryFederationPkiAndTransportNeutral(): void
    {
        foreach ([
            CredentialTrustChainEvaluatorInterface::class,
            CredentialTrustProfilePolicyInterface::class,
            CredentialTrustEntityRolePolicyInterface::class,
            CredentialTrustProfile::class,
            CredentialTrustEntityReference::class,
            CredentialTrustChainContext::class,
            CredentialTrustChainAssessment::class,
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
            self::assertStringNotContainsString('Firebase', $source);
        }
    }
}
