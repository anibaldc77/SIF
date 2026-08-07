<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

final readonly class FederatedRevocationResult
{
    public function __construct(
        private bool $localSessionsRevoked,
        private bool $providerCredentialsRevoked,
        private bool $identityLinkRevoked
    ) {
    }

    public function localSessionsRevoked(): bool
    {
        return $this->localSessionsRevoked;
    }

    public function providerCredentialsRevoked(): bool
    {
        return $this->providerCredentialsRevoked;
    }

    public function identityLinkRevoked(): bool
    {
        return $this->identityLinkRevoked;
    }
}
