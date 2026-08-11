<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;
use Sif\Foundation\Security\WebAuthn\WebAuthnDiscoverableCredentialProfile;

interface WebAuthnDiscoverableCredentialPolicyInterface
{
    public function validate(
        WebAuthnCredential $credential,
        WebAuthnDiscoverableCredentialProfile $profile
    ): void;
}
