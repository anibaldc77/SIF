<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialNonceServiceInterface;
use Sif\Foundation\Security\Contracts\CredentialProofFingerprintResolverInterface;
use Sif\Foundation\Security\Contracts\CredentialProofReplayStoreInterface;
use Sif\Foundation\Security\Contracts\CredentialProofVerifierInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialNonce;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationResult;

final class OpenId4VciProofOfPossessionNonceAndReplayProtectionTest extends TestCase
{
    public function testIssuanceProofKeepsTypePayloadAndAttributesExplicit(): void
    {
        $proof = new CredentialIssuanceProof(
            'jwt',
            'serialized-proof',
            ['kid' => 'holder-key-001']
        );

        self::assertSame('jwt', $proof->proofType());
        self::assertSame('serialized-proof', $proof->serializedProof());
        self::assertSame('holder-key-001', $proof->attributes()['kid']);
    }

    public function testCredentialNonceKeepsLifecycleExplicit(): void
    {
        $nonce = new CredentialNonce(
            'nonce-001',
            new DateTimeImmutable('2026-08-10T16:00:00Z'),
            new DateTimeImmutable('2026-08-10T16:05:00Z')
        );

        self::assertSame('nonce-001', $nonce->value());
        self::assertNotNull($nonce->expiresAt());
    }

    public function testProofValidationContextBindsIssuerClientSubjectAndNonce(): void
    {
        $context = new CredentialProofValidationContext(
            'https://issuer.example.test',
            'wallet-client-001',
            'user-001',
            $this->nonce(),
            new DateTimeImmutable('2026-08-10T16:01:00Z')
        );

        self::assertSame(
            'https://issuer.example.test',
            $context->credentialIssuer()
        );
        self::assertSame('wallet-client-001', $context->clientId());
        self::assertSame('user-001', $context->subjectId());
        self::assertSame('nonce-001', $context->nonce()->value());
    }

    public function testValidationResultAggregatesNonceHolderAndReplayChecks(): void
    {
        $result = new CredentialProofValidationResult(
            true,
            true,
            true,
            true
        );

        self::assertTrue($result->valid());
        self::assertTrue($result->nonceValid());
        self::assertTrue($result->holderBound());
        self::assertTrue($result->replaySafe());
    }

    public function testInvalidReplayMakesOverallResultInvalid(): void
    {
        $result = new CredentialProofValidationResult(
            true,
            true,
            true,
            false,
            ['proof_replayed']
        );

        self::assertFalse($result->valid());
        self::assertSame(
            ['proof_replayed'],
            $result->violations()
        );
    }

    public function testProofContractsAreTyped(): void
    {
        $nonce = new \ReflectionMethod(
            CredentialNonceServiceInterface::class,
            'issue'
        );
        $verify = new \ReflectionMethod(
            CredentialProofVerifierInterface::class,
            'verify'
        );
        $fingerprint = new \ReflectionMethod(
            CredentialProofFingerprintResolverInterface::class,
            'fingerprint'
        );

        self::assertSame(
            CredentialNonce::class,
            (string) $nonce->getReturnType()
        );
        self::assertSame(
            CredentialProofValidationResult::class,
            (string) $verify->getReturnType()
        );
        self::assertSame(
            'string',
            (string) $fingerprint->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                CredentialProofReplayStoreInterface::class
            ))->isInterface()
        );
    }

    public function testProofLayerRemainsCryptoAndStorageNeutral(): void
    {
        foreach ([
            CredentialNonceServiceInterface::class,
            CredentialProofVerifierInterface::class,
            CredentialProofReplayStoreInterface::class,
            CredentialProofFingerprintResolverInterface::class,
            CredentialIssuanceProof::class,
            CredentialNonce::class,
            CredentialProofValidationContext::class,
            CredentialProofValidationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('Lcobucci', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    private function nonce(): CredentialNonce
    {
        return new CredentialNonce(
            'nonce-001',
            new DateTimeImmutable('2026-08-10T16:00:00Z'),
            new DateTimeImmutable('2026-08-10T16:05:00Z')
        );
    }
}
