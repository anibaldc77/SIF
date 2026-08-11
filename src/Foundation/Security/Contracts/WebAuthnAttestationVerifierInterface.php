<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnAttestationStatement;
use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticatorMetadata;

interface WebAuthnAttestationVerifierInterface
{
    public function verify(
        WebAuthnAttestationStatement $attestation,
        ?WebAuthnAuthenticatorMetadata $metadata
    ): void;
}
