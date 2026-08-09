<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthAuthorizationRequest
{
    /**
     * @param list<OAuthScope> $scopes
     */
    public function __construct(
        private OAuthClientId $clientId,
        private OAuthRedirectUri $redirectUri,
        private string $responseType,
        private array $scopes,
        private ?string $state = null
    ) {
    }

    public function clientId(): OAuthClientId
    {
        return $this->clientId;
    }

    public function redirectUri(): OAuthRedirectUri
    {
        return $this->redirectUri;
    }

    public function responseType(): string
    {
        return $this->responseType;
    }

    /**
     * @return list<OAuthScope>
     */
    public function scopes(): array
    {
        return $this->scopes;
    }

    public function state(): ?string
    {
        return $this->state;
    }
}
