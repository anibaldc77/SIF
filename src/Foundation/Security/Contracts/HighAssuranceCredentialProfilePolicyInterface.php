<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialProfile;

interface HighAssuranceCredentialProfilePolicyInterface
{
    public function validate(
        HighAssuranceCredentialProfile $profile
    ): void;
}
