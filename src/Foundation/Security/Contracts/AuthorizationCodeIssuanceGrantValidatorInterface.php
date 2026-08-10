<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\AuthorizationCodeIssuanceGrant;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceGrantAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceGrantContext;

interface AuthorizationCodeIssuanceGrantValidatorInterface
{
    public function validate(
        AuthorizationCodeIssuanceGrant $grant,
        CredentialIssuanceGrantContext $context
    ): CredentialIssuanceGrantAssessment;
}
