<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;

interface WebAuthnAuthenticatorPolicyInterface
{
    public function validate(WebAuthnCredential $credential): void;
}
