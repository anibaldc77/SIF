<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticationContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;
use Sif\Foundation\Security\WebAuthn\WebAuthnVerificationResult;

interface WebAuthnAuthenticationVerifierInterface
{
    public function verify(
        string $clientDataJson,
        string $authenticatorData,
        string $signature,
        WebAuthnCredential $credential,
        WebAuthnAuthenticationContext $context
    ): WebAuthnVerificationResult;
}
