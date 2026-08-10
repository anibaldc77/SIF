<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationDetail;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationTransactionBinding;

interface CredentialAuthorizationTransactionBinderInterface
{
    public function bind(
        string $transactionId,
        CredentialAuthorizationDetail $detail,
        CredentialAuthorizationContext $context
    ): CredentialAuthorizationTransactionBinding;
}
