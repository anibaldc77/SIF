<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;

interface CredentialTrustProfilePolicyInterface
{
    public function validate(
        CredentialTrustProfile $profile
    ): void;
}
