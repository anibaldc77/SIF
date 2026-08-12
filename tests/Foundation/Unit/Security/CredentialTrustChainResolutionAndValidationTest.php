<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialTrustChainLinkPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustChainValidationPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChain;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChainCycleDetector;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChainLink;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChainValidationResult;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityRole;

final class CredentialTrustChainResolutionAndValidationTest extends TestCase
{
    private function entity(
        string $id,
        CredentialTrustEntityRole $role
    ): CredentialTrustEntityReference {
        return new CredentialTrustEntityReference(
            $id,
            $role,
            'ecosystem-alpha'
        );
    }

    public function testChainKeepsOrderedLinksDepthLeafAndRootExplicit(): void
    {
        $chain = new CredentialTrustChain([
            new CredentialTrustChainLink(
                $this->entity('https://issuer.example.test', CredentialTrustEntityRole::Issuer),
                'https://authority.example.test',
                ['accreditation-001'],
                ['issuer-key-001']
            ),
            new CredentialTrustChainLink(
                $this->entity('https://authority.example.test', CredentialTrustEntityRole::AccreditationAuthority),
                'https://trust-anchor.example.test',
                ['authority-accreditation-001'],
                ['authority-key-001']
            ),
            new CredentialTrustChainLink(
                $this->entity('https://trust-anchor.example.test', CredentialTrustEntityRole::TrustAnchor),
                null,
                [],
                ['anchor-key-001']
            ),
        ]);

        self::assertSame(3, $chain->depth());
        self::assertSame('https://issuer.example.test', $chain->leaf()->entity()->entityId());
        self::assertSame('https://trust-anchor.example.test', $chain->root()->entity()->entityId());
    }

    public function testChainLinkKeepsParentAccreditationsAndKeysExplicit(): void
    {
        $link = new CredentialTrustChainLink(
            $this->entity('https://issuer.example.test', CredentialTrustEntityRole::Issuer),
            'https://authority.example.test',
            ['accreditation-001'],
            ['issuer-key-001']
        );

        self::assertSame('https://authority.example.test', $link->parentEntityId());
        self::assertSame(['accreditation-001'], $link->accreditationIds());
        self::assertSame(['issuer-key-001'], $link->keyMaterialIds());
    }

    public function testCycleDetectorRejectsRepeatedEntityIds(): void
    {
        $chain = new CredentialTrustChain([
            new CredentialTrustChainLink(
                $this->entity('https://issuer.example.test', CredentialTrustEntityRole::Issuer),
                'https://authority.example.test'
            ),
            new CredentialTrustChainLink(
                $this->entity('https://authority.example.test', CredentialTrustEntityRole::AccreditationAuthority),
                'https://issuer.example.test'
            ),
            new CredentialTrustChainLink(
                $this->entity('https://issuer.example.test', CredentialTrustEntityRole::Issuer),
                null
            ),
        ]);

        self::assertTrue((new CredentialTrustChainCycleDetector())->hasCycle($chain));
    }

    public function testCycleDetectorAcceptsAcyclicChain(): void
    {
        $chain = new CredentialTrustChain([
            new CredentialTrustChainLink(
                $this->entity('https://issuer.example.test', CredentialTrustEntityRole::Issuer),
                'https://trust-anchor.example.test'
            ),
            new CredentialTrustChainLink(
                $this->entity('https://trust-anchor.example.test', CredentialTrustEntityRole::TrustAnchor),
                null
            ),
        ]);

        self::assertFalse((new CredentialTrustChainCycleDetector())->hasCycle($chain));
    }

    public function testValidationResultRepresentsValidAndBlockedStates(): void
    {
        $valid = new CredentialTrustChainValidationResult(true);

        self::assertTrue($valid->valid());
        self::assertSame([], $valid->violations());
        self::assertSame([], $valid->warnings());

        $blocked = new CredentialTrustChainValidationResult(
            false,
            ['trust_anchor_not_allowed'],
            ['metadata freshness requires review']
        );

        self::assertFalse($blocked->valid());
        self::assertSame(['trust_anchor_not_allowed'], $blocked->violations());
        self::assertSame(['metadata freshness requires review'], $blocked->warnings());
    }

    public function testTrustChainContractsAreTypedAndSeparated(): void
    {
        $resolver = new \ReflectionMethod(
            CredentialTrustChainResolverInterface::class,
            'resolve'
        );
        $validator = new \ReflectionMethod(
            CredentialTrustChainValidationPolicyInterface::class,
            'validate'
        );

        self::assertSame(CredentialTrustChain::class, (string) $resolver->getReturnType());
        self::assertSame(
            CredentialTrustChainValidationResult::class,
            (string) $validator->getReturnType()
        );
        self::assertTrue(
            (new \ReflectionClass(
                CredentialTrustChainLinkPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testArchitecturePreservesI1ToI3Contracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\CredentialTrustChainEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustRegistryResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialAccreditationResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustAnchorResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustKeyMaterialResolverInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testChainResolutionLayerRemainsFederationPkiTransportAndStorageNeutral(): void
    {
        foreach ([
            CredentialTrustChainResolverInterface::class,
            CredentialTrustChainValidationPolicyInterface::class,
            CredentialTrustChainLinkPolicyInterface::class,
            CredentialTrustChain::class,
            CredentialTrustChainLink::class,
            CredentialTrustChainValidationResult::class,
            CredentialTrustChainCycleDetector::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

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
