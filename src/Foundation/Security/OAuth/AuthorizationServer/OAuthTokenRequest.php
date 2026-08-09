<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthTokenRequest
{
    /**
     * @param array<string, scalar|null> $parameters
     */
    public function __construct(
        private string $grantType,
        private OAuthClientId $clientId,
        private array $parameters = []
    ) {
    }

    public function grantType(): string
    {
        return $this->grantType;
    }

    public function clientId(): OAuthClientId
    {
        return $this->clientId;
    }

    /**
     * @return array<string, scalar|null>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }
}
