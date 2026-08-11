<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnRecoveryContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnRecoveryDecision;

interface WebAuthnRecoveryPolicyInterface
{
    public function decide(
        WebAuthnRecoveryContext $context
    ): WebAuthnRecoveryDecision;
}
