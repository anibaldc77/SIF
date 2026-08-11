<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;
use Sif\Foundation\Security\WebAuthn\WebAuthnRegistrationContext;

interface WebAuthnRegistrationVerifierInterface
{
    public function verify(
        string $clientDataJson,
        string $attestationObject,
        WebAuthnRegistrationContext $context
    ): WebAuthnCredential;
}
