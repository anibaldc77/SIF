<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\DeferredCredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\DeferredCredentialIssuanceResult;

interface DeferredCredentialIssuanceServiceInterface
{
    public function resolve(
        DeferredCredentialIssuanceRequest $request
    ): DeferredCredentialIssuanceResult;
}
