<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusContext;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusEvidence;

interface CredentialFreshnessPolicyInterface
{
    public function validate(
        CredentialStatusEvidence $evidence,
        CredentialStatusContext $context
    ): void;
}
