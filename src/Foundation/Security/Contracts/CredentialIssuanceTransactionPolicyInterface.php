<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceTransaction;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\DeferredCredentialIssuanceRequest;

interface CredentialIssuanceTransactionPolicyInterface
{
    public function validate(
        CredentialIssuanceTransaction $transaction,
        DeferredCredentialIssuanceRequest $request
    ): void;
}
