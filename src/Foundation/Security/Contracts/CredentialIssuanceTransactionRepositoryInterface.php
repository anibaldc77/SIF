<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceTransaction;

interface CredentialIssuanceTransactionRepositoryInterface
{
    public function find(
        string $transactionId
    ): ?CredentialIssuanceTransaction;

    public function save(
        CredentialIssuanceTransaction $transaction
    ): void;

    public function delete(
        string $transactionId
    ): void;
}
