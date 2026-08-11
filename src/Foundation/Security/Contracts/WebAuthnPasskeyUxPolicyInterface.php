<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnDiscoverableCredentialProfile;
use Sif\Foundation\Security\WebAuthn\WebAuthnPasskeyUxContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnPasskeyUxDecision;

interface WebAuthnPasskeyUxPolicyInterface
{
    public function decide(
        WebAuthnPasskeyUxContext $context,
        WebAuthnDiscoverableCredentialProfile $profile
    ): WebAuthnPasskeyUxDecision;
}
