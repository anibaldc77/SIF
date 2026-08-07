<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlAuthenticationResult
{
    public function __construct(
        private SamlAuthenticatedIdentity $identity,
        private bool $sessionEstablished
    ) {
    }

    public function identity(): SamlAuthenticatedIdentity
    {
        return $this->identity;
    }

    public function sessionEstablished(): bool
    {
        return $this->sessionEstablished;
    }
}
