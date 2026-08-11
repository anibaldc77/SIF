<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SdJwtVcDisclosureDigestVerifierInterface;
use Sif\Foundation\Security\Contracts\SdJwtVcKeyBindingPolicyInterface;
use Sif\Foundation\Security\Contracts\SdJwtVcSelectiveDisclosurePolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcCredentialPayload;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcDisclosure;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcDisclosureReference;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcKeyBindingContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcSelectiveDisclosureSet;

final class SdJwtVcDataModelAndSelectiveDisclosureBoundariesTest extends TestCase
{
    public function testDisclosureKeepsSaltClaimNameAndValueExplicit(): void
    {
        $disclosure = new SdJwtVcDisclosure(
            'salt-001',
            'given_name',
            'Alice'
        );

        self::assertSame('salt-001', $disclosure->salt());
        self::assertSame('given_name', $disclosure->claimName());
        self::assertSame('Alice', $disclosure->claimValue());
    }

    public function testDisclosureReferenceKeepsDigestAndAlgorithmExplicit(): void
    {
        $reference = new SdJwtVcDisclosureReference(
            'digest-001',
            'sha-256'
        );

        self::assertSame('digest-001', $reference->digest());
        self::assertSame('sha-256', $reference->hashAlgorithm());
    }

    public function testCredentialPayloadKeepsIssuerTypeClaimsAndReferencesExplicit(): void
    {
        $payload = new SdJwtVcCredentialPayload(
            'https://issuer.example.test',
            'https://credentials.example.test/identity',
            ['exp' => 2000000000],
            [
                new SdJwtVcDisclosureReference(
                    'digest-001'
                ),
            ],
            'subject-001'
        );

        self::assertSame(
            'https://issuer.example.test',
            $payload->issuer()
        );
        self::assertSame(
            'https://credentials.example.test/identity',
            $payload->vct()
        );
        self::assertSame(2000000000, $payload->claims()['exp']);
        self::assertCount(1, $payload->disclosureReferences());
        self::assertSame('subject-001', $payload->subject());
    }

    public function testSelectiveDisclosureSetKeepsDisclosedAndUndisclosedStateExplicit(): void
    {
        $set = new SdJwtVcSelectiveDisclosureSet(
            [
                new SdJwtVcDisclosure(
                    'salt-001',
                    'given_name',
                    'Alice'
                ),
            ],
            ['digest-hidden-001']
        );

        self::assertCount(1, $set->disclosures());
        self::assertSame(
            ['digest-hidden-001'],
            $set->undisclosedDigests()
        );
    }

    public function testKeyBindingContextKeepsAudienceNonceAndHolderKeyExplicit(): void
    {
        $context = new SdJwtVcKeyBindingContext(
            'https://verifier.example.test',
            'nonce-001',
            'holder-key-001'
        );

        self::assertSame(
            'https://verifier.example.test',
            $context->audience()
        );
        self::assertSame('nonce-001', $context->nonce());
        self::assertSame(
            'holder-key-001',
            $context->holderKeyId()
        );
    }

    public function testSelectiveDisclosureContractsAreTyped(): void
    {
        $digest = new \ReflectionMethod(
            SdJwtVcDisclosureDigestVerifierInterface::class,
            'matches'
        );

        self::assertSame(
            'bool',
            (string) $digest->getReturnType()
        );

        foreach ([
            SdJwtVcSelectiveDisclosurePolicyInterface::class,
            SdJwtVcKeyBindingPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testSdJwtVcModelRemainsJwtHashAndCryptoImplementationNeutral(): void
    {
        foreach ([
            SdJwtVcDisclosureDigestVerifierInterface::class,
            SdJwtVcSelectiveDisclosurePolicyInterface::class,
            SdJwtVcKeyBindingPolicyInterface::class,
            SdJwtVcDisclosure::class,
            SdJwtVcDisclosureReference::class,
            SdJwtVcCredentialPayload::class,
            SdJwtVcSelectiveDisclosureSet::class,
            SdJwtVcKeyBindingContext::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('hash(', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Guzzle', $source);
        }
    }
}
