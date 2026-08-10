<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\CredentialTrustAssessment;
use Sif\Foundation\Security\VerifiableCredentials\CredentialTrustContext;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

interface CredentialTrustPolicyInterface
{
    public function assess(
        VerifiableCredential $credential,
        CredentialTrustContext $context
    ): CredentialTrustAssessment;
}
