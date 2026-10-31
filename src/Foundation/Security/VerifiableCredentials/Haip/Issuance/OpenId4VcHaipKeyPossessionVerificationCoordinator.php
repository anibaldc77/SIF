<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use Sif\Foundation\Security\Contracts\CredentialProofVerifierInterface;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofRequirementsResolverInterface;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyPossessionVerifierInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationResult;

final readonly class OpenId4VcHaipKeyPossessionVerificationCoordinator
{
    public function __construct(
        private CredentialProofVerifierInterface $proofVerifier,
        private OpenId4VcHaipKeyPossessionVerifierInterface $possessionVerifier,
        private OpenId4VcHaipKeyPossessionEvidenceEvaluator $evidenceEvaluator,
        private \Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofPolicyInterface $conformancePolicy,
        private \Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationVerifierInterface $attestationVerifier,
        private OpenId4VcHaipCredentialProofRequirementsResolverInterface $requirementsResolver
    ) {
    }

    public function assess(
        CredentialIssuanceProof $proof,
        CredentialProofValidationContext $validationContext,
        OpenId4VcHaipCredentialProofContext $haipContext,
        ?CredentialConfiguration $configuration = null
    ): OpenId4VcHaipCredentialProofAssessment {
        if ($validationContext->credentialIssuer() !== $haipContext->issuerIdentifier()) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_proof_issuer_context_mismatch']
            );
        }

        $validationResult = $this->proofVerifier->verify(
            $proof,
            $validationContext
        );

        if ($proof->proofType() === 'attestation') {
            return $this->assessAttestation(
                $proof,
                $haipContext,
                $configuration,
                $validationResult
            );
        }
        if (!$validationResult->valid()) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_proof_validation_failed']
            );
        }

        $possessionResult = $this->possessionVerifier->verify(
            $proof,
            $validationContext
        );

        if ($configuration === null) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_configuration_not_verified']
            );
        }

        if ($configuration->configurationId() !== $haipContext->credentialConfigurationId()) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_configuration_mismatch']
            );
        }

        $verifiedProofType = $possessionResult->verifiedProofType();

        if (
            !$possessionResult->valid()
            || $verifiedProofType === null
            || $verifiedProofType !== $proof->proofType()
        ) {
            $possessionAssessment = $this->evidenceEvaluator->assess(
                $proof,
                $validationContext,
                $validationResult,
                $possessionResult,
                new OpenId4VcHaipCredentialProofContext(
                    issuerIdentifier: $haipContext->issuerIdentifier(),
                    credentialConfigurationId: $haipContext->credentialConfigurationId(),
                    requestedKeyReferences: $haipContext->requestedKeyReferences(),
                    proofVerified: $validationResult->valid(),
                    nonceVerified: $validationResult->nonceValid(),
                    replaySafe: $validationResult->replaySafe(),
                    keyAttestationRequired: false
                )
            );

            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                $possessionAssessment->violations(),
                $possessionAssessment->warnings()
            );
        }

        if (!in_array($verifiedProofType, $configuration->proofTypes(), true)) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_proof_mechanism_not_supported']
            );
        }

        $requirements = $this->requirementsResolver->resolve(
            $configuration,
            $verifiedProofType
        );

        $verifiedHaipContext = $this->verifiedHaipContext(
            $haipContext,
            $validationResult,
            $requirements->keyAttestationRequired()
        );

        $possessionAssessment = $this->evidenceEvaluator->assess(
            $proof,
            $validationContext,
            $validationResult,
            $possessionResult,
            $verifiedHaipContext
        );

        $conformanceAssessment = $this->conformancePolicy->assess(
            $verifiedHaipContext
        );

        $violations = array_values(array_unique(array_merge(
            $possessionAssessment->violations(),
            $conformanceAssessment->violations()
        )));

        $warnings = array_values(array_unique(array_merge(
            $possessionAssessment->warnings(),
            $conformanceAssessment->warnings()
        )));

        return new OpenId4VcHaipCredentialProofAssessment(
            $possessionAssessment->conformant()
                && $conformanceAssessment->conformant(),
            $violations,
            $warnings
        );
    }

    private function assessAttestation(
        CredentialIssuanceProof $proof,
        OpenId4VcHaipCredentialProofContext $context,
        ?CredentialConfiguration $configuration,
        CredentialProofValidationResult $validationResult
    ): OpenId4VcHaipCredentialProofAssessment {
        if (!$validationResult->validWithoutHolderBinding()) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_proof_validation_failed']
            );
        }

        if ($configuration === null) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_configuration_not_verified']
            );
        }

        if ($configuration->configurationId() !== $context->credentialConfigurationId()) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_configuration_mismatch']
            );
        }

        if (!in_array('attestation', $configuration->proofTypes(), true)) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['credential_proof_mechanism_not_supported']
            );
        }

        $attestation = $context->keyAttestation();
        if ($attestation === null) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['key_attestation_evidence_missing']
            );
        }

        if ($attestation->attestationFormat() !== 'jwt') {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['key_attestation_format_not_supported']
            );
        }

        // A fingerprint is metadata, not the signed evidence. The specialized
        // adapter must receive the exact compact JWS submitted in this proof.
        $serializedJwt = $attestation->serializedJwt();
        if ($serializedJwt === null || $serializedJwt === '') {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['key_attestation_serialized_evidence_missing']
            );
        }

        if (!hash_equals($proof->serializedProof(), $serializedJwt)) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['key_attestation_serialized_evidence_mismatch']
            );
        }
        $identity = $attestation->evidenceIdentity();
        if ($identity === null) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['key_attestation_evidence_identity_missing']
            );
        }

        // The verified attestation MUST belong to the submitted JWT, not to
        // an unrelated key-attestation object supplied alongside the request.
        $expectedIdentity = new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            hash('sha256', $proof->serializedProof())
        );
        if (!$identity->matches($expectedIdentity)) {
            return new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['key_attestation_evidence_identity_mismatch']
            );
        }

        // Disregard caller-supplied verification flags or result objects.
        // The trusted adapter must authenticate the attestation itself.
        $verification = $this->attestationVerifier->verify(
            $attestation,
            $context->issuerIdentifier()
        );

        $verifiedContext = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: $context->issuerIdentifier(),
            credentialConfigurationId: $context->credentialConfigurationId(),
            requestedKeyReferences: $context->requestedKeyReferences(),
            proofVerified: $validationResult->validWithoutHolderBinding(),
            nonceVerified: $validationResult->nonceValid(),
            replaySafe: $validationResult->replaySafe(),
            keyAttestationRequired: true,
            keyAttestation: $attestation,
            keyAttestationVerification: $verification
        );

        return $this->conformancePolicy->assess($verifiedContext);
    }
    private function verifiedHaipContext(
        OpenId4VcHaipCredentialProofContext $context,
        CredentialProofValidationResult $validationResult,
        bool $keyAttestationRequired
    ): OpenId4VcHaipCredentialProofContext {
        $verification = null;

        if ($keyAttestationRequired) {
            $attestation = $context->keyAttestation();

            if ($attestation !== null) {
                $verification = $this->attestationVerifier->verify(
                    $attestation,
                    $context->issuerIdentifier()
                );
            }
        }

        return new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: $context->issuerIdentifier(),
            credentialConfigurationId: $context->credentialConfigurationId(),
            requestedKeyReferences: $context->requestedKeyReferences(),
            proofVerified: $validationResult->valid(),
            nonceVerified: $validationResult->nonceValid(),
            replaySafe: $validationResult->replaySafe(),
            keyAttestationRequired: $keyAttestationRequired,
            keyAttestation: $context->keyAttestation(),
            keyAttestationVerification: $verification
        );
    }
}