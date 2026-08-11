<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticatorMetadata;

interface WebAuthnAuthenticatorMetadataResolverInterface
{
    public function resolve(
        string $authenticatorIdentifier
    ): ?WebAuthnAuthenticatorMetadata;
}
