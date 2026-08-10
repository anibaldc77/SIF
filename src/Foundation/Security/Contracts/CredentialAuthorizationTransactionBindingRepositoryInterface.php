<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialAuthorizationTransactionBinding;

interface CredentialAuthorizationTransactionBindingRepositoryInterface
{
    public function find(
        string $transactionId
    ): ?CredentialAuthorizationTransactionBinding;

    public function save(
        CredentialAuthorizationTransactionBinding $binding
    ): void;

    public function delete(
        string $transactionId
    ): void;
}
