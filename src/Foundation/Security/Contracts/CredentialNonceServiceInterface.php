<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialNonce;

interface CredentialNonceServiceInterface
{
    public function issue(): CredentialNonce;

    public function rotate(
        CredentialNonce $current
    ): CredentialNonce;
}
