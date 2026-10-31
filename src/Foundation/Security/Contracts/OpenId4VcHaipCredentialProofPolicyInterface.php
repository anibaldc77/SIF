<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofContext;

interface OpenId4VcHaipCredentialProofPolicyInterface
{
    public function assess(
        OpenId4VcHaipCredentialProofContext $context
    ): OpenId4VcHaipCredentialProofAssessment;
}
