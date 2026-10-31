<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofRequirementsResolverInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionVerificationCoordinator;

final class OpenId4VcHaipCredentialProofRequirementsIntegrationTest extends TestCase
{
    public function testCallerCannotDisableRequiredKeyAttestation(): void
    {
        $proof = new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof(
            'jwt',
            'header.payload.signature'
        );

        $validationContext = new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationContext(
            'https://issuer.example',
            'client-1',
            'subject-1',
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialNonce(
                'nonce-1',
                new \DateTimeImmutable('2026-10-23T11:55:00+00:00'),
                new \DateTimeImmutable('2026-10-23T12:05:00+00:00')
            ),
            new \DateTimeImmutable('2026-10-23T12:00:00+00:00')
        );

        $haipContext = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: false
        );

        $configuration = new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration(
            'identity',
            'vc+sd-jwt',
            ['jwk'],
            ['jwt']
        );

        $generic = $this->createMock(
            \Sif\Foundation\Security\Contracts\CredentialProofVerifierInterface::class
        );

        $generic->method('verify')->willReturn(
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationResult(
                true,
                true,
                true,
                true
            )
        );

        $possession = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyPossessionVerifierInterface::class
        );

        $possession->method('verify')->willReturn(
            new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionVerificationResult(
                true,
                ['key-1'],
                [],
                'https://issuer.example',
                \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
                    $proof
                ),
                'jwt'
            )
        );

        $resolver = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofRequirementsResolverInterface::class
        );

        $resolver->expects(self::once())
            ->method('resolve')
            ->with(
                self::identicalTo($configuration),
                self::identicalTo('jwt')
            )
            ->willReturn(
                new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofRequirements(
                    true
                )
            );

        $attestationVerifier = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $attestationVerifier->expects(self::never())
            ->method('verify');

        $coordinator = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionVerificationCoordinator(
            $generic,
            $possession,
            new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionEvidenceEvaluator(),
            new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofConformancePolicy(),
            $attestationVerifier,
            $resolver
        );

        $assessment = $coordinator->assess(
            $proof,
            $validationContext,
            $haipContext,
            $configuration
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_evidence_missing',
            $assessment->violations()
        );
    }
    public function testCoordinatorRequiresTrustedRequirementsResolver(): void
    {
        $constructor = new ReflectionMethod(
            OpenId4VcHaipKeyPossessionVerificationCoordinator::class,
            '__construct'
        );

        $resolverParameters = array_filter(
            $constructor->getParameters(),
            static function (\ReflectionParameter $parameter): bool {
                $type = $parameter->getType();

                return $type instanceof \ReflectionNamedType
                    && $type->getName()
                        === OpenId4VcHaipCredentialProofRequirementsResolverInterface::class
                    && !$parameter->isOptional();
            }
        );

        self::assertCount(1, $resolverParameters);
    }
}