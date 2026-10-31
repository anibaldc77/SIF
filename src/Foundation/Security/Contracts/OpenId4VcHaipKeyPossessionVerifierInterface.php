<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionVerificationResult;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationContext;

interface OpenId4VcHaipKeyPossessionVerifierInterface
{
    public function verify(
        CredentialIssuanceProof $proof,
        CredentialProofValidationContext $context
    ): OpenId4VcHaipKeyPossessionVerificationResult;
}