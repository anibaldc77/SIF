<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationTransactionBinding;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceTransaction;

interface CredentialAuthorizationTransactionPolicyInterface
{
    public function validate(
        CredentialAuthorizationTransactionBinding $binding,
        CredentialIssuanceTransaction $transaction
    ): void;
}
