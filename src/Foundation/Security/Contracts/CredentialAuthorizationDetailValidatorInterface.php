<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationDetail;

interface CredentialAuthorizationDetailValidatorInterface
{
    public function validate(
        CredentialAuthorizationDetail $detail,
        CredentialAuthorizationContext $context
    ): CredentialAuthorizationAssessment;
}
