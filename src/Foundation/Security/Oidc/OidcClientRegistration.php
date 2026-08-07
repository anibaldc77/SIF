<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class OidcClientRegistration
{
    public function __construct(
        private string $clientId,
        private string $redirectUri,
        private bool $confidential = false
    ) {
        if ($this->clientId === '' || strlen($this->clientId) > 512) {
            throw new InvalidArgumentException(
                'OIDC client identifier is invalid.'
            );
        }

        if ($this->redirectUri === '' || strlen($this->redirectUri) > 2048) {
            throw new InvalidArgumentException(
                'OIDC redirect URI is invalid.'
            );
        }
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function redirectUri(): string
    {
        return $this->redirectUri;
    }

    public function isConfidential(): bool
    {
        return $this->confidential;
    }
}
