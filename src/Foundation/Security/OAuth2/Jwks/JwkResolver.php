<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwks;

use Sif\Foundation\Security\Contracts\JwkSetProviderInterface;

final readonly class JwkResolver
{
    public function __construct(
        private JwkSetProviderInterface $provider
    ) {
    }

    public function resolve(string $keyId): ?Jwk
    {
        $key = $this->provider->get()->find($keyId);

        if ($key !== null) {
            return $key;
        }

        return $this->provider->refresh()->find($keyId);
    }
}
