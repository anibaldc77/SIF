<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;
use Sif\Foundation\Security\WebAuthn\WebAuthnCredentialLifecycleEvent;

interface WebAuthnCredentialLifecyclePolicyInterface
{
    public function validateTransition(
        WebAuthnCredential $credential,
        WebAuthnCredentialLifecycleEvent $event
    ): void;
}
