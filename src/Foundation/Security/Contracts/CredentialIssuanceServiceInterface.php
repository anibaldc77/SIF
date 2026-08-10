<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceResponse;

interface CredentialIssuanceServiceInterface
{
    public function issue(
        CredentialIssuanceRequest $request,
        CredentialIssuanceContext $context
    ): CredentialIssuanceResponse;
}
