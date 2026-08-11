<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;
use Sif\Foundation\Security\WebAuthn\WebAuthnPasskeyUxContext;

interface WebAuthnCredentialSelectionPolicyInterface
{
    /**
     * @param list<WebAuthnCredential> $credentials
     * @return list<WebAuthnCredential>
     */
    public function select(
        array $credentials,
        WebAuthnPasskeyUxContext $context
    ): array;
}
