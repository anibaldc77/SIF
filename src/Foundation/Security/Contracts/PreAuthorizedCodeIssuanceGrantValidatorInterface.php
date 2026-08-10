<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceGrantAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceGrantContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\PreAuthorizedCodeIssuanceGrant;

interface PreAuthorizedCodeIssuanceGrantValidatorInterface
{
    public function validate(
        PreAuthorizedCodeIssuanceGrant $grant,
        CredentialIssuanceGrantContext $context
    ): CredentialIssuanceGrantAssessment;
}
