<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusProfile;

interface CredentialStatusProfilePolicyInterface
{
    public function validate(
        CredentialStatusProfile $profile
    ): void;
}
