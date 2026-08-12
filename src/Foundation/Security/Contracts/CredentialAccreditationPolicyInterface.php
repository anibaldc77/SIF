<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation\CredentialAccreditation;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;

interface CredentialAccreditationPolicyInterface
{
    public function validate(
        CredentialAccreditation $accreditation,
        CredentialTrustProfile $profile
    ): void;
}
