<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofConformancePolicy;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofContext;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult;

final class OpenId4VcHaipCredentialProofConformancePolicyTest extends TestCase
{
    public function testValidAttestationCoveringAllKeysIsAccepted(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1', 'key-2'],
            verification: new OpenId4VcHaipKeyAttestationVerificationResult(true, ['key-1', 'key-2'], [], 'https://issuer.example', new OpenId4VcHaipKeyAttestationEvidenceIdentity(str_repeat('a', 64)))
        );

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }

    public function testMissingAttestationFailsClosed(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: null
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_evidence_missing',
            $assessment->violations()
        );
    }

    public function testIncompleteKeyCoverageIsRejected(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1', 'key-2'],
            verification: new OpenId4VcHaipKeyAttestationVerificationResult(true, ['key-1'], [], 'https://issuer.example', new OpenId4VcHaipKeyAttestationEvidenceIdentity(str_repeat('a', 64)))
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_key_coverage_incomplete',
            $assessment->violations()
        );
    }

    public function testInvalidAttestationIsRejected(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: new OpenId4VcHaipKeyAttestationVerificationResult(
                false,
                [],
                ['signature_invalid']
            )
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_verification_failed',
            $assessment->violations()
        );
    }

    public function testUnverifiedCredentialProofIsRejected(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: $this->validVerification(),
            proofVerified: false
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'credential_proof_not_verified',
            $assessment->violations()
        );
    }

    public function testUnverifiedNonceIsRejected(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: $this->validVerification(),
            nonceVerified: false
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'credential_proof_nonce_not_verified',
            $assessment->violations()
        );
    }

    public function testMissingReplayProtectionIsRejected(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: $this->validVerification(),
            replaySafe: false
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'credential_proof_replay_protection_not_verified',
            $assessment->violations()
        );
    }

    public function testAttestationCanBeOptionalWhenPolicyDoesNotRequireIt(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: null,
            keyAttestationRequired: false
        );

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }

    public function testAuthenticatedWalletProviderDifferentFromCredentialIssuerIsAccepted(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: new OpenId4VcHaipKeyAttestationVerificationResult(
                true,
                ['key-1'],
                [],
                'https://wallet-provider.example',
                new OpenId4VcHaipKeyAttestationEvidenceIdentity(str_repeat('a', 64))
            )
        );

        self::assertTrue($assessment->conformant());
        self::assertSame([], $assessment->violations());
    }
    public function testAttestationWithoutVerifiedIssuerIsRejected(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: new OpenId4VcHaipKeyAttestationVerificationResult(
                true,
                ['key-1']
            )
        );

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_issuer_unverified',
            $assessment->violations()
        );
    }
    public function testEmptyVerifiedKeyAttestationIssuerIsRejected(): void
    {
        $assessment = $this->assess(
            requiredKeys: ['key-1'],
            verification: new OpenId4VcHaipKeyAttestationVerificationResult(
                true,
                ['key-1'],
                [],
                '   ',
                new OpenId4VcHaipKeyAttestationEvidenceIdentity(str_repeat('a', 64))
            )
        );

        self::assertFalse($assessment->conformant());
        self::assertContains('key_attestation_issuer_unverified', $assessment->violations());
    }
    public function testAttestationWithoutEvidenceIdentityIsRejected(): void
    {
        $context = $this->contextWithEvidenceIdentities(
            presentedIdentity: null,
            verifiedIdentity: new OpenId4VcHaipKeyAttestationEvidenceIdentity(
                str_repeat('a', 64)
            )
        );

        $assessment = (new OpenId4VcHaipCredentialProofConformancePolicy())
            ->assess($context);

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_evidence_identity_missing',
            $assessment->violations()
        );
    }

    public function testVerificationWithoutEvidenceIdentityIsRejected(): void
    {
        $context = $this->contextWithEvidenceIdentities(
            presentedIdentity: new OpenId4VcHaipKeyAttestationEvidenceIdentity(
                str_repeat('a', 64)
            ),
            verifiedIdentity: null
        );

        $assessment = (new OpenId4VcHaipCredentialProofConformancePolicy())
            ->assess($context);

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_evidence_identity_missing',
            $assessment->violations()
        );
    }

    public function testSubstitutedEvidenceIdentityIsRejected(): void
    {
        $context = $this->contextWithEvidenceIdentities(
            presentedIdentity: new OpenId4VcHaipKeyAttestationEvidenceIdentity(
                str_repeat('a', 64)
            ),
            verifiedIdentity: new OpenId4VcHaipKeyAttestationEvidenceIdentity(
                str_repeat('b', 64)
            )
        );

        $assessment = (new OpenId4VcHaipCredentialProofConformancePolicy())
            ->assess($context);

        self::assertFalse($assessment->conformant());
        self::assertContains(
            'key_attestation_evidence_identity_mismatch',
            $assessment->violations()
        );
    }

    private function contextWithEvidenceIdentities(
        ?OpenId4VcHaipKeyAttestationEvidenceIdentity $presentedIdentity,
        ?OpenId4VcHaipKeyAttestationEvidenceIdentity $verifiedIdentity
    ): OpenId4VcHaipCredentialProofContext {
        return new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: true,
            keyAttestation: new OpenId4VcHaipKeyAttestation(
                'jwt',
                ['key-1'],
                $presentedIdentity
            ),
            keyAttestationVerification: new OpenId4VcHaipKeyAttestationVerificationResult(
                true,
                ['key-1'],
                [],
                'https://issuer.example',
                $verifiedIdentity
            )
        );
    }
    private function validVerification(): OpenId4VcHaipKeyAttestationVerificationResult
    {
        return new OpenId4VcHaipKeyAttestationVerificationResult(true, ['key-1'], [], 'https://issuer.example', new OpenId4VcHaipKeyAttestationEvidenceIdentity(str_repeat('a', 64)));
    }

    /**
     * @param non-empty-list<string> $requiredKeys
     */
    private function assess(
        array $requiredKeys,
        ?OpenId4VcHaipKeyAttestationVerificationResult $verification,
        bool $proofVerified = true,
        bool $nonceVerified = true,
        bool $replaySafe = true,
        bool $keyAttestationRequired = true
    ): OpenId4VcHaipCredentialProofAssessment {
        $context = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: 'identity',
            requestedKeyReferences: $requiredKeys,
            proofVerified: $proofVerified,
            nonceVerified: $nonceVerified,
            replaySafe: $replaySafe,
            keyAttestationRequired: $keyAttestationRequired,
            keyAttestation: $verification === null
                ? null
                : new OpenId4VcHaipKeyAttestation(
                    'jwt',
                    $requiredKeys,
                    new OpenId4VcHaipKeyAttestationEvidenceIdentity(
                        str_repeat('a', 64)
                    )
                ),
            keyAttestationVerification: $verification
        );

        return (new OpenId4VcHaipCredentialProofConformancePolicy())
            ->assess($context);
    }
}