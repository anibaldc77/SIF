<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProfile;

interface HighAssuranceCredentialFormatPolicyInterface
{
    public function validateProfile(
        CredentialFormatProfile $profile
    ): void;
}
