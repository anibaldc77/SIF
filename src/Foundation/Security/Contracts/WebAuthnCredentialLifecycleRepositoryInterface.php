<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCredentialLifecycleEvent;

interface WebAuthnCredentialLifecycleRepositoryInterface
{
    public function append(
        WebAuthnCredentialLifecycleEvent $event
    ): void;

    /**
     * @return list<WebAuthnCredentialLifecycleEvent>
     */
    public function history(
        string $credentialId
    ): array;
}
