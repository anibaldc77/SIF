<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\BatchCredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\BatchCredentialIssuanceResponse;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceContext;

interface BatchCredentialIssuanceServiceInterface
{
    public function issueBatch(
        BatchCredentialIssuanceRequest $request,
        CredentialIssuanceContext $context
    ): BatchCredentialIssuanceResponse;
}
