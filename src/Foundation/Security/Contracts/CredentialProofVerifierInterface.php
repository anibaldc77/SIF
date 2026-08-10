<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialProofValidationResult;

interface CredentialProofVerifierInterface
{
    public function verify(
        CredentialIssuanceProof $proof,
        CredentialProofValidationContext $context
    ): CredentialProofValidationResult;
}
