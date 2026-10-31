<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Security\Contracts\CredentialProofVerifierInterface;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyPossessionVerifierInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofContext;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionEvidenceEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionProofIdentity;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionVerificationCoordinator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionVerificationResult;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialNonce;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationResult;

final class OpenId4VcHaipKeyPossessionVerificationCoordinatorTest extends TestCase
{
    public function testValidVerificationIsAccepted(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $coordinator = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof)
        );

        $assessment = $coordinator->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }

    public function testInvalidGenericVerificationStopsPossessionVerification(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->expects(self::once())
            ->method('verify')
            ->willReturn(new CredentialProofValidationResult(false, true, true, true));

        $possession = $this->createMock(
            OpenId4VcHaipKeyPossessionVerifierInterface::class
        );
        $possession->expects(self::never())->method('verify');

        $assessment = $this->coordinator($generic, $possession)
            ->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'credential_proof_validation_failed',
            $assessment->violations()
        );
    }

    public function testInvalidPossessionVerificationIsRejected(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $possession = $this->createMock(
            OpenId4VcHaipKeyPossessionVerifierInterface::class
        );
        $possession->method('verify')->willReturn(
            new OpenId4VcHaipKeyPossessionVerificationResult(false, [])
        );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $possession
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'credential_proof_key_possession_not_verified',
            $assessment->violations()
        );
    }

    public function testInvalidPossessionDoesNotResolveRequirementsOrVerifyAttestation(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $possession = $this->createMock(
            OpenId4VcHaipKeyPossessionVerifierInterface::class
        );

        $possession->expects(self::once())
            ->method('verify')
            ->willReturn(new OpenId4VcHaipKeyPossessionVerificationResult(false, []));

        $resolver = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofRequirementsResolverInterface::class
        );

        $resolver->expects(self::never())
            ->method('resolve');

        $attestationVerifier = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $attestationVerifier->expects(self::never())
            ->method('verify');

        $coordinator = new OpenId4VcHaipKeyPossessionVerificationCoordinator(
            $this->genericVerifier(),
            $possession,
            new OpenId4VcHaipKeyPossessionEvidenceEvaluator(),
            new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofConformancePolicy(),
            $attestationVerifier,
            $resolver
        );

        $assessment = $coordinator->assess(
            $proof,
            $context,
            $haipContext,
            $this->credentialConfiguration()
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'credential_proof_key_possession_not_verified',
            $assessment->violations()
        );
    }
    public function testDifferentProofIdentityIsRejected(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $different = new CredentialIssuanceProof(
            'jwt',
            'different.serialized.proof'
        );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($different)
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'credential_proof_identity_mismatch',
            $assessment->violations()
        );
    }

    public function testDifferentVerifiedProofTypeIsRejected(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof, 'other')
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'credential_proof_type_mismatch',
            $assessment->violations()
        );
    }

    public function testBothVerifiersReceiveTheSameProofAndContext(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->expects(self::once())
            ->method('verify')
            ->with(self::identicalTo($proof), self::identicalTo($context))
            ->willReturn(new CredentialProofValidationResult(true, true, true, true));

        $possession = $this->createMock(
            OpenId4VcHaipKeyPossessionVerifierInterface::class
        );
        $possession->expects(self::once())
            ->method('verify')
            ->with(self::identicalTo($proof), self::identicalTo($context))
            ->willReturn($this->validPossessionResult($proof));

        $assessment = $this->coordinator($generic, $possession)
            ->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertTrue($assessment->conformant());
    }

    public function testGenericVerifierExceptionIsPropagated(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')
            ->willThrowException(new RuntimeException('Generic verifier failed.'));

        $possession = $this->createMock(
            OpenId4VcHaipKeyPossessionVerifierInterface::class
        );
        $possession->expects(self::never())->method('verify');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Generic verifier failed.');

        $this->coordinator($generic, $possession)
            ->assess($proof, $context, $haipContext, $this->credentialConfiguration());
    }

    public function testPossessionVerifierExceptionIsPropagated(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $possession = $this->createMock(
            OpenId4VcHaipKeyPossessionVerifierInterface::class
        );
        $possession->method('verify')
            ->willThrowException(new RuntimeException('Possession verifier failed.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Possession verifier failed.');

        $this->coordinator(
            $this->genericVerifier(),
            $possession
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());
    }

    public function testRequiredKeyAttestationWithoutEvidenceIsRejected(): void
    {
        [$proof, $context] = $this->fixtures();

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: true
        );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof),
            null,
            null,
            true
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_evidence_missing',
            $assessment->violations()
        );
    }

    public function testRequiredKeyAttestationWithInvalidVerificationIsRejected(): void
    {
        [$proof, $context] = $this->fixtures();

        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('a', 64)
        );

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: true,
            keyAttestation: new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation(
                'jwt',
                ['key-1'],
                $identity
            ),
            keyAttestationVerification: new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(
                false,
                [],
                ['signature_invalid']
            )
        );

        $verifier = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $verifier->expects(self::once())
            ->method('verify')
            ->willReturn(
                new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(
                    false,
                    [],
                    ['signature_invalid']
                )
            );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof),
            $verifier,
            null,
            true
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_verification_failed',
            $assessment->violations()
        );
    }

    public function testOptionalKeyAttestationWithoutEvidenceIsAccepted(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof)
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }

    public function testRequiredValidKeyAttestationIsAccepted(): void
    {
        [$proof, $context] = $this->fixtures();

        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('a', 64)
        );

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: true,
            keyAttestation: new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation(
                'jwt',
                ['key-1'],
                $identity
            ),
            keyAttestationVerification: new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(
                true,
                ['key-1'],
                [],
                'https://issuer.example',
                $identity
            )
        );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof),
            null,
            null,
            true
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }
    public function testExternallySuppliedValidAttestationCannotOverrideVerifierFailure(): void
    {
        [$proof, $context] = $this->fixtures();

        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('a', 64)
        );

        $attestation = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation(
            'jwt',
            ['key-1'],
            $identity
        );

        $externallySuppliedResult = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(
            true,
            ['key-1'],
            [],
            'https://issuer.example',
            $identity
        );

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: true,
            keyAttestation: $attestation,
            keyAttestationVerification: $externallySuppliedResult
        );

        $verifier = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $verifier->expects(self::once())
            ->method('verify')
            ->willReturn(
                new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(
                    false,
                    [],
                    ['signature_invalid']
                )
            );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof),
            $verifier,
            null,
            true
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_verification_failed',
            $assessment->violations()
        );
    }

    public function testAttestationVerifierReceivesPresentedEvidenceAndIssuer(): void
    {
        [$proof, $context] = $this->fixtures();

        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('a', 64)
        );

        $attestation = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation(
            'jwt',
            ['key-1'],
            $identity
        );

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: true,
            keyAttestation: $attestation
        );

        $verifier = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $verifier->expects(self::once())
            ->method('verify')
            ->with(
                self::identicalTo($attestation),
                self::identicalTo('https://issuer.example')
            )
            ->willReturn(
                new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(
                    true,
                    ['key-1'],
                    [],
                    'https://issuer.example',
                    $identity
                )
            );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof),
            $verifier,
            null,
            true
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }
    public function testIssuerContextMismatchRejectsBeforeVerifierInvocation(): void
    {
        [$proof, $context] = $this->fixtures();

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://different-issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: true
        );

        $generic = $this->createMock(
            CredentialProofVerifierInterface::class
        );

        $generic->expects(self::never())
            ->method('verify');

        $possession = $this->createMock(
            OpenId4VcHaipKeyPossessionVerifierInterface::class
        );

        $possession->expects(self::never())
            ->method('verify');

        $attestation = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $attestation->expects(self::never())
            ->method('verify');

        $assessment = $this->coordinator(
            $generic,
            $possession,
            $attestation
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertSame(
            ['credential_proof_issuer_context_mismatch'],
            $assessment->violations()
        );
    }
    public function testPolicyWarningsArePreservedByCoordinator(): void
    {
        [$proof, $context, $haipContext] = $this->fixtures();

        $policy = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofPolicyInterface::class
        );

        $policy->expects(self::once())
            ->method('assess')
            ->willReturn(
                new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofAssessment(
                    true,
                    [],
                    ['policy_warning']
                )
            );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof),
            null,
            $policy
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
        self::assertSame(['policy_warning'], $assessment->warnings());
    }
    public function testMissingRequiredAttestationDoesNotInvokeVerifier(): void
    {
        [$proof, $context] = $this->fixtures();

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: true
        );

        $attestationVerifier = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $attestationVerifier->expects(self::never())
            ->method('verify');

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof),
            $attestationVerifier,
            null,
            true
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_evidence_missing',
            $assessment->violations()
        );
    }
    public function testVerifiedProofResultDoesNotDependOnCallerSuppliedFlags(): void
    {
        [$proof, $context] = $this->fixtures();

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: false,
            nonceVerified: false,
            replaySafe: false,
            keyAttestationRequired: false
        );

        $assessment = $this->coordinator(
            $this->genericVerifier(),
            $this->possessionVerifier($proof)
        )->assess($proof, $context, $haipContext, $this->credentialConfiguration());

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }
    public function testAttestationProofDoesNotRequireIndependentKeyPossession(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');

        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(hash('sha256', $proof->serializedProof()));
        $attestation = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation('jwt', ['key-1'], $identity, $proof->serializedProof());

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: false,
            nonceVerified: false,
            replaySafe: false,
            keyAttestationRequired: false,
            keyAttestation: $attestation
        );

        $configuration = new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration(
            'identity', 'vc+sd-jwt', ['jwk'], ['jwt', 'attestation']
        );

        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(
            new CredentialProofValidationResult(true, true, false, true)
        );

        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');

        $attestationVerifier = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $attestationVerifier->expects(self::once())->method('verify')
            ->with(self::identicalTo($attestation), 'https://issuer.example')
            ->willReturn(new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(
                true, ['key-1'], [], 'https://issuer.example', $identity
            ));

        $assessment = $this->coordinator(
            $generic, $possession, $attestationVerifier, null, true
        )->assess($proof, $validationContext, $haipContext, $configuration);

        self::assertTrue($assessment->conformant(), implode(', ', $assessment->violations()));
        self::assertSame([], $assessment->violations());
    }

    public function testAttestationProofRejectsUnboundEvidence(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');
        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(str_repeat('a', 64));
        $attestation = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation('jwt', ['key-1'], $identity, $proof->serializedProof());
        $context = new OpenId4VcHaipCredentialProofContext('https://issuer.example', 'identity', ['key-1'], false, false, false, false, $attestation);
        $verifier = $this->createMock(\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class);
        $verifier->expects(self::never())->method('verify');
        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(new CredentialProofValidationResult(true, true, false, true));
        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');

        $assessment = $this->coordinator($generic, $possession, $verifier)->assess(
            $proof, $validationContext, $context,
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration('identity', 'vc+sd-jwt', ['jwk'], ['attestation'])
        );
        self::assertFalse($assessment->conformant());
        self::assertContains('key_attestation_evidence_identity_mismatch', $assessment->violations());
    }

    public function testAttestationProofRejectsFailedCryptoVerification(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');
        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(hash('sha256', $proof->serializedProof()));
        $attestation = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation('jwt', ['key-1'], $identity, $proof->serializedProof());
        $context = new OpenId4VcHaipCredentialProofContext('https://issuer.example', 'identity', ['key-1'], false, false, false, false, $attestation);
        $verifier = $this->createMock(\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class);
        $verifier->expects(self::once())->method('verify')->willReturn(
            new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(false, [], ['signature_invalid'])
        );
        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(new CredentialProofValidationResult(true, true, false, true));
        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');

        $assessment = $this->coordinator($generic, $possession, $verifier)->assess(
            $proof, $validationContext, $context,
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration('identity', 'vc+sd-jwt', ['jwk'], ['attestation'])
        );
        self::assertFalse($assessment->conformant());
        self::assertContains('key_attestation_verification_failed', $assessment->violations());
    }

    public function testAttestationProofRejectsIncompleteKeyCoverage(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');
        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(hash('sha256', $proof->serializedProof()));
        $attestation = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation('jwt', ['key-1'], $identity, $proof->serializedProof());
        $context = new OpenId4VcHaipCredentialProofContext('https://issuer.example', 'identity', ['key-1', 'key-2'], false, false, false, false, $attestation);
        $verifier = $this->createMock(\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class);
        $verifier->method('verify')->willReturn(
            new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(true, ['key-1'], [], 'https://issuer.example', $identity)
        );
        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(new CredentialProofValidationResult(true, true, false, true));
        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');

        $assessment = $this->coordinator($generic, $possession, $verifier)->assess(
            $proof, $validationContext, $context,
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration('identity', 'vc+sd-jwt', ['jwk'], ['attestation'])
        );
        self::assertFalse($assessment->conformant());
        self::assertContains('key_attestation_key_coverage_incomplete', $assessment->violations());
    }

    public function testAttestationProofRejectsUnsupportedMechanism(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');
        $context = new OpenId4VcHaipCredentialProofContext('https://issuer.example', 'identity', ['key-1'], false, false, false, false);
        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(new CredentialProofValidationResult(true, true, false, true));
        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');
        $verifier = $this->createMock(\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class);
        $verifier->expects(self::never())->method('verify');

        $assessment = $this->coordinator($generic, $possession, $verifier)->assess(
            $proof, $validationContext, $context, $this->credentialConfiguration()
        );
        self::assertFalse($assessment->conformant());
        self::assertContains('credential_proof_mechanism_not_supported', $assessment->violations());
    }

    public function testAttestationProofRejectsFailedFreshness(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');
        $context = new OpenId4VcHaipCredentialProofContext('https://issuer.example', 'identity', ['key-1'], false, false, false, false);
        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(new CredentialProofValidationResult(true, false, false, true));
        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');
        $verifier = $this->createMock(\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class);
        $verifier->expects(self::never())->method('verify');

        $assessment = $this->coordinator($generic, $possession, $verifier)->assess(
            $proof, $validationContext, $context,
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration('identity', 'vc+sd-jwt', ['jwk'], ['attestation'])
        );
        self::assertFalse($assessment->conformant());
        self::assertContains('credential_proof_validation_failed', $assessment->violations());
    }

    public function testAttestationProofRejectsMissingKeyAttestation(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');
        $context = new OpenId4VcHaipCredentialProofContext('https://issuer.example', 'identity', ['key-1'], false, false, false, false);
        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(new CredentialProofValidationResult(true, true, false, true));
        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');
        $verifier = $this->createMock(\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class);
        $verifier->expects(self::never())->method('verify');

        $assessment = $this->coordinator($generic, $possession, $verifier)->assess(
            $proof, $validationContext, $context,
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration('identity', 'vc+sd-jwt', ['jwk'], ['attestation'])
        );
        self::assertFalse($assessment->conformant());
        self::assertContains('key_attestation_evidence_missing', $assessment->violations());
    }
    public function testAttestationProofRejectsMissingSerializedEvidence(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');
        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(hash('sha256', $proof->serializedProof()));
        $attestation = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation('jwt', ['key-1'], $identity);
        $context = new OpenId4VcHaipCredentialProofContext('https://issuer.example', 'identity', ['key-1'], false, false, false, false, $attestation);
        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(new CredentialProofValidationResult(true, true, false, true));
        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');
        $verifier = $this->createMock(\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class);
        $verifier->expects(self::never())->method('verify');
        $assessment = $this->coordinator($generic, $possession, $verifier)->assess(
            $proof, $validationContext, $context,
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration('identity', 'vc+sd-jwt', ['jwk'], ['attestation'])
        );
        self::assertFalse($assessment->conformant());
        self::assertContains('key_attestation_serialized_evidence_missing', $assessment->violations());
    }

    public function testAttestationProofRejectsDifferentSerializedEvidence(): void
    {
        [, $validationContext] = $this->fixtures();
        $proof = new CredentialIssuanceProof('attestation', 'header.payload.signature');
        $identity = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(hash('sha256', $proof->serializedProof()));
        $attestation = new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation('jwt', ['key-1'], $identity, 'different.jwt.token');
        $context = new OpenId4VcHaipCredentialProofContext('https://issuer.example', 'identity', ['key-1'], false, false, false, false, $attestation);
        $generic = $this->createMock(CredentialProofVerifierInterface::class);
        $generic->method('verify')->willReturn(new CredentialProofValidationResult(true, true, false, true));
        $possession = $this->createMock(OpenId4VcHaipKeyPossessionVerifierInterface::class);
        $possession->expects(self::never())->method('verify');
        $verifier = $this->createMock(\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class);
        $verifier->expects(self::never())->method('verify');
        $assessment = $this->coordinator($generic, $possession, $verifier)->assess(
            $proof, $validationContext, $context,
            new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration('identity', 'vc+sd-jwt', ['jwk'], ['attestation'])
        );
        self::assertFalse($assessment->conformant());
        self::assertContains('key_attestation_serialized_evidence_mismatch', $assessment->violations());
    }
    private function coordinator(
        CredentialProofVerifierInterface $generic,
        OpenId4VcHaipKeyPossessionVerifierInterface $possession,
        ?\Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface $attestationVerifier = null,
        ?\Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofPolicyInterface $policy = null,
        bool $keyAttestationRequired = false
    ): OpenId4VcHaipKeyPossessionVerificationCoordinator {
        return new OpenId4VcHaipKeyPossessionVerificationCoordinator(
            $generic,
            $possession,
            new OpenId4VcHaipKeyPossessionEvidenceEvaluator(),
            $policy ?? new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofConformancePolicy(),
            $attestationVerifier ?? $this->attestationVerifier(),
            $this->requirementsResolver($keyAttestationRequired)
        );
    }

    private function credentialConfiguration(): \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration
    {
        return new \Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration(
            'identity',
            'vc+sd-jwt',
            ['jwk'],
            ['jwt']
        );
    }
    private function requirementsResolver(bool $keyAttestationRequired): \Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofRequirementsResolverInterface
    {
        $resolver = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofRequirementsResolverInterface::class
        );

        $resolver->method('resolve')->willReturn(
            new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofRequirements(
                $keyAttestationRequired
            )
        );

        return $resolver;
    }
    private function attestationVerifier(): \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface
    {
        $verifier = $this->createMock(
            \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface::class
        );

        $verifier->method('verify')->willReturn(
            new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult(
                true,
                ['key-1'],
                [],
                'https://issuer.example',
                new \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity(
                    str_repeat('a', 64)
                )
            )
        );

        return $verifier;
    }
    private function genericVerifier(): CredentialProofVerifierInterface
    {
        $verifier = $this->createMock(CredentialProofVerifierInterface::class);
        $verifier->method('verify')
            ->willReturn(new CredentialProofValidationResult(true, true, true, true));

        return $verifier;
    }

    private function possessionVerifier(
        CredentialIssuanceProof $verifiedProof,
        ?string $verifiedProofType = 'jwt'
    ): OpenId4VcHaipKeyPossessionVerifierInterface {
        $verifier = $this->createMock(
            OpenId4VcHaipKeyPossessionVerifierInterface::class
        );
        $verifier->method('verify')->willReturn(
            $this->validPossessionResult($verifiedProof, $verifiedProofType)
        );

        return $verifier;
    }

    private function validPossessionResult(
        CredentialIssuanceProof $verifiedProof,
        ?string $verifiedProofType = 'jwt'
    ): OpenId4VcHaipKeyPossessionVerificationResult {
        return new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            ['key-1'],
            [],
            'https://issuer.example',
            OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
                $verifiedProof
            ),
            $verifiedProofType
        );
    }

    /**
     * @return array{
     *     CredentialIssuanceProof,
     *     CredentialProofValidationContext,
     *     OpenId4VcHaipCredentialProofContext
     * }
     */
    private function fixtures(): array
    {
        $proof = new CredentialIssuanceProof(
            'jwt',
            'header.payload.signature'
        );

        $context = new CredentialProofValidationContext(
            'https://issuer.example',
            'client-1',
            'subject-1',
            new CredentialNonce(
                'nonce-1',
                new DateTimeImmutable('2026-10-23T11:55:00+00:00'),
                new DateTimeImmutable('2026-10-23T12:05:00+00:00')
            ),
            new DateTimeImmutable('2026-10-23T12:00:00+00:00')
        );

        $haipContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: false
        );

        return [$proof, $context, $haipContext];
    }
}