<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnAttestationStatement;
use Sif\Foundation\Security\WebAuthn\WebAuthnAttestationTrustAssessment;
use Sif\Foundation\Security\WebAuthn\WebAuthnAttestationTrustContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticatorMetadata;

interface WebAuthnAttestationTrustPolicyInterface
{
    public function assess(
        WebAuthnAttestationStatement $attestation,
        ?WebAuthnAuthenticatorMetadata $metadata,
        WebAuthnAttestationTrustContext $context
    ): WebAuthnAttestationTrustAssessment;
}
