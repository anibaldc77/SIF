<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;

interface CredentialTrustEntityRolePolicyInterface
{
    public function validate(
        CredentialTrustEntityReference $entity,
        CredentialTrustProfile $profile
    ): void;
}
