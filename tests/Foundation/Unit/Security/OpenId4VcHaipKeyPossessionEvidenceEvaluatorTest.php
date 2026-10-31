<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofContext;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionEvidenceEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionProofIdentity;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionVerificationResult;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialNonce;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationResult;

final class OpenId4VcHaipKeyPossessionEvidenceEvaluatorTest extends TestCase
{
    public function testConsistentEvidenceIsAccepted(): void
    {
        $result = $this->assess();

        self::assertTrue($result->conformant());
        self::assertSame([], $result->violations());
    }

    public function testDifferentValidationIssuerIsRejected(): void
    {
        $result = $this->assess(validationIssuer: 'https://other.example');

        self::assertContains(
            'credential_proof_issuer_context_mismatch',
            $result->violations()
        );
    }

    public function testInvalidGenericVerificationIsRejected(): void
    {
        $result = $this->assess(
            validationResult: new CredentialProofValidationResult(
                false,
                true,
                true,
                true
            )
        );

        self::assertContains(
            'credential_proof_validation_failed',
            $result->violations()
        );
    }

    public function testInvalidNonceIsRejected(): void
    {
        $result = $this->assess(
            validationResult: new CredentialProofValidationResult(
                true,
                false,
                true,
                true
            )
        );

        self::assertContains(
            'credential_proof_nonce_not_verified',
            $result->violations()
        );
    }

    public function testMissingHolderBindingIsRejected(): void
    {
        $result = $this->assess(
            validationResult: new CredentialProofValidationResult(
                true,
                true,
                false,
                true
            )
        );

        self::assertContains(
            'credential_proof_holder_binding_not_verified',
            $result->violations()
        );
    }

    public function testUnsafeReplayIsRejected(): void
    {
        $result = $this->assess(
            validationResult: new CredentialProofValidationResult(
                true,
                true,
                true,
                false
            )
        );

        self::assertContains(
            'credential_proof_replay_protection_not_verified',
            $result->violations()
        );
    }

    public function testInvalidKeyPossessionIsRejected(): void
    {
        $result = $this->assess(possessionValid: false);

        self::assertContains(
            'credential_proof_key_possession_not_verified',
            $result->violations()
        );
    }

    public function testDifferentProofIdentityIsRejected(): void
    {
        $result = $this->assess(
            verifiedProof: new CredentialIssuanceProof(
                'jwt',
                'different.serialized.proof'
            )
        );

        self::assertContains(
            'credential_proof_identity_mismatch',
            $result->violations()
        );
    }

    public function testIncompleteKeyCoverageIsRejected(): void
    {
        $result = $this->assess(
            requiredKeys: ['key-1', 'key-2'],
            verifiedKeys: ['key-1']
        );

        self::assertContains(
            'credential_proof_key_coverage_incomplete',
            $result->violations()
        );
    }

    public function testDifferentPossessionIssuerIsRejected(): void
    {
        $result = $this->assess(
            possessionIssuer: 'https://other.example'
        );

        self::assertContains(
            'credential_proof_possession_issuer_mismatch',
            $result->violations()
        );
    }

    public function testUnverifiedHaipProofIsRejected(): void
    {
        $result = $this->assess(haipProofVerified: false);

        self::assertFalse($result->conformant());
        self::assertContains(
            'credential_proof_not_verified',
            $result->violations()
        );
    }

    public function testUnverifiedHaipNonceIsRejected(): void
    {
        $result = $this->assess(haipNonceVerified: false);

        self::assertFalse($result->conformant());
        self::assertContains(
            'credential_proof_nonce_not_verified',
            $result->violations()
        );
    }

    public function testUnsafeHaipReplayIsRejected(): void
    {
        $result = $this->assess(haipReplaySafe: false);

        self::assertFalse($result->conformant());
        self::assertContains(
            'credential_proof_replay_protection_not_verified',
            $result->violations()
        );
    }
    public function testDifferentVerifiedProofTypeIsRejected(): void
    {
        $result = $this->assess(
            verifiedProofType: 'other'
        );

        self::assertFalse($result->conformant());
        self::assertContains(
            'credential_proof_type_mismatch',
            $result->violations()
        );
    }

    public function testMissingVerifiedProofTypeIsRejected(): void
    {
        $result = $this->assess(
            verifiedProofType: null
        );

        self::assertFalse($result->conformant());
        self::assertContains(
            'credential_proof_type_mismatch',
            $result->violations()
        );
    }

    public function testDifferentMechanismWithSameProofBytesIsRejected(): void
    {
        $result = $this->assess(
            verifiedProof: new CredentialIssuanceProof(
                'other',
                'header.payload.signature'
            ),
            verifiedProofType: 'other'
        );

        self::assertFalse($result->conformant());
        self::assertContains(
            'credential_proof_type_mismatch',
            $result->violations()
        );
    }
    /**
     * @param non-empty-list<string> $requiredKeys
     * @param list<string> $verifiedKeys
     */
    private function assess(
        string $validationIssuer = 'https://issuer.example',
        ?CredentialProofValidationResult $validationResult = null,
        bool $possessionValid = true,
        ?CredentialIssuanceProof $verifiedProof = null,
        array $requiredKeys = ['key-1'],
        array $verifiedKeys = ['key-1'],
        string $possessionIssuer = 'https://issuer.example',
        ?string $verifiedProofType = 'jwt',
        bool $haipProofVerified = true,
        bool $haipNonceVerified = true,
        bool $haipReplaySafe = true
    ): \Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofAssessment {
        $proof = new CredentialIssuanceProof(
            'jwt',
            'header.payload.signature'
        );

        $verifiedProof ??= $proof;

        $possessionResult = new OpenId4VcHaipKeyPossessionVerificationResult(
            $possessionValid,
            $possessionValid ? $verifiedKeys : [],
            [],
            $possessionIssuer,
            OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
                $verifiedProof
            ),
            $verifiedProofType
        );

        $validationResult ??= new CredentialProofValidationResult(
            true,
            true,
            true,
            true
        );

        $validationContext = new CredentialProofValidationContext(
            $validationIssuer,
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
            requestedKeyReferences: $requiredKeys,
            proofVerified: $haipProofVerified,
            nonceVerified: $haipNonceVerified,
            replaySafe: $haipReplaySafe,
            keyAttestationRequired: false
        );

        return (new OpenId4VcHaipKeyPossessionEvidenceEvaluator())
            ->assess(
                $proof,
                $validationContext,
                $validationResult,
                $possessionResult,
                $haipContext
            );
    }
}