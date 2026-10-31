<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofPolicyInterface;

final readonly class OpenId4VcHaipCredentialProofConformancePolicy implements OpenId4VcHaipCredentialProofPolicyInterface
{
    public function assess(
        OpenId4VcHaipCredentialProofContext $context
    ): OpenId4VcHaipCredentialProofAssessment {
        $violations = [];

        if (!$context->proofVerified()) {
            $violations[] = 'credential_proof_not_verified';
        }

        if (!$context->nonceVerified()) {
            $violations[] = 'credential_proof_nonce_not_verified';
        }

        if (!$context->replaySafe()) {
            $violations[] = 'credential_proof_replay_protection_not_verified';
        }

        if ($context->keyAttestationRequired()) {
            $verification = $context->keyAttestationVerification();

            if ($context->keyAttestation() === null || $verification === null) {
                $violations[] = 'key_attestation_evidence_missing';
            } elseif (!$verification->valid()) {
                $violations[] = 'key_attestation_verification_failed';
            } elseif ($verification->verifiedIssuerIdentifier() === null
                || trim($verification->verifiedIssuerIdentifier()) === ''
            ) {
                // The specialized verifier must authenticate the attestation issuer
                // under the Credential Issuer's trust policy. The attestation signer
                // may be a Wallet Provider or key-storage component; it is NOT
                // required to equal the Credential Issuer identifier.
                $violations[] = 'key_attestation_issuer_unverified';
            } elseif (
                $context->keyAttestation()->evidenceIdentity() === null
                || $verification->verifiedEvidenceIdentity() === null
            ) {
                $violations[] = 'key_attestation_evidence_identity_missing';
            } elseif (
                !$context->keyAttestation()->evidenceIdentity()->matches(
                    $verification->verifiedEvidenceIdentity()
                )
            ) {
                $violations[] = 'key_attestation_evidence_identity_mismatch';
            } elseif (!$verification->coversAllKeys(
                $context->requestedKeyReferences()
            )) {
                $violations[] = 'key_attestation_key_coverage_incomplete';
            }
        }

        return new OpenId4VcHaipCredentialProofAssessment(
            $violations === [],
            $violations
        );
    }
}