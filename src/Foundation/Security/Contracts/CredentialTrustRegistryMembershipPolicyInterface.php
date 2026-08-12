<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Registry\CredentialTrustRegistryEntry;

interface CredentialTrustRegistryMembershipPolicyInterface
{
    public function validate(
        CredentialTrustRegistryEntry $entry,
        CredentialTrustProfile $profile
    ): void;
}
