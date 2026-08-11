<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusPurpose;
use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusAllocation;

interface CredentialStatusAllocationRepositoryInterface
{
    public function findByCredentialId(
        string $credentialId
    ): ?CredentialStatusAllocation;

    public function allocate(
        string $credentialId,
        string $statusListId,
        CredentialStatusPurpose $purpose
    ): CredentialStatusAllocation;
}
