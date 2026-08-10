<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceRequest;

interface CredentialIssuanceProofValidatorInterface
{
    public function validate(
        CredentialIssuanceRequest $request,
        CredentialIssuanceContext $context
    ): void;
}
