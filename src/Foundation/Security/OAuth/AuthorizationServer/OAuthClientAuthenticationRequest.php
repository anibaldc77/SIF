<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthClientAuthenticationRequest
{
    public function __construct(
        private OAuthClientId $clientId,
        private OAuthClientAuthenticationMethod $method,
        private ?OAuthClientCredential $credential = null
    ) {
    }

    public function clientId(): OAuthClientId
    {
        return $this->clientId;
    }

    public function method(): OAuthClientAuthenticationMethod
    {
        return $this->method;
    }

    public function credential(): ?OAuthClientCredential
    {
        return $this->credential;
    }
}
