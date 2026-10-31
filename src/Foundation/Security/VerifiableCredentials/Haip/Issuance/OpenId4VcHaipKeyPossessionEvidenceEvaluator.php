<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationResult;

final readonly class OpenId4VcHaipKeyPossessionEvidenceEvaluator
{
    public function assess(
        CredentialIssuanceProof $proof,
        CredentialProofValidationContext $validationContext,
        CredentialProofValidationResult $validationResult,
        OpenId4VcHaipKeyPossessionVerificationResult $possessionResult,
        OpenId4VcHaipCredentialProofContext $haipContext
    ): OpenId4VcHaipCredentialProofAssessment {
        $violations = [];

        if ($validationContext->credentialIssuer() !== $haipContext->issuerIdentifier()) {
            $violations[] = 'credential_proof_issuer_context_mismatch';
        }

        if (!$haipContext->proofVerified()) {
            $violations[] = 'credential_proof_not_verified';
        }

        if (!$haipContext->nonceVerified()) {
            $violations[] = 'credential_proof_nonce_not_verified';
        }

        if (!$haipContext->replaySafe()) {
            $violations[] = 'credential_proof_replay_protection_not_verified';
        }

        if (!$validationResult->valid()) {
            $violations[] = 'credential_proof_validation_failed';
        }

        if (!$validationResult->nonceValid()) {
            $violations[] = 'credential_proof_nonce_not_verified';
        }

        if (!$validationResult->holderBound()) {
            $violations[] = 'credential_proof_holder_binding_not_verified';
        }

        if (!$validationResult->replaySafe()) {
            $violations[] = 'credential_proof_replay_protection_not_verified';
        }

        if (!$possessionResult->valid()) {
            $violations[] = 'credential_proof_key_possession_not_verified';
        }

        if (
            $possessionResult->verifiedIssuerIdentifier() === null
            || $possessionResult->verifiedIssuerIdentifier()
                !== $validationContext->credentialIssuer()
        ) {
            $violations[] = 'credential_proof_possession_issuer_mismatch';
        }

        if (
            $possessionResult->verifiedProofType() === null
            || $possessionResult->verifiedProofType() !== $proof->proofType()
        ) {
            $violations[] = 'credential_proof_type_mismatch';
        }
        $expectedIdentity = OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
            $proof
        );

        $verifiedIdentity = $possessionResult->verifiedProofIdentity();

        if (
            $verifiedIdentity === null
            || !$expectedIdentity->matches($verifiedIdentity)
        ) {
            $violations[] = 'credential_proof_identity_mismatch';
        }

        if (!$possessionResult->coversAllKeys($haipContext->requestedKeyReferences())) {
            $violations[] = 'credential_proof_key_coverage_incomplete';
        }

        return new OpenId4VcHaipCredentialProofAssessment(
            $violations === [],
            $violations
        );
    }
}