<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusLifecycleTransition;

interface CredentialStatusLifecyclePolicyInterface
{
    public function validate(
        CredentialStatusLifecycleTransition $transition
    ): void;
}
