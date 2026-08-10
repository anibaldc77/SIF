<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusAssessment;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusContext;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusEvidence;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

interface CredentialStatusPolicyInterface
{
    public function assess(
        VerifiableCredential $credential,
        CredentialStatusEvidence $evidence,
        CredentialStatusContext $context
    ): CredentialStatusAssessment;
}
