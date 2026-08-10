<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusContext;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusEvidence;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

interface CredentialStatusResolverInterface
{
    public function resolve(
        VerifiableCredential $credential,
        CredentialStatusContext $context
    ): CredentialStatusEvidence;
}
