<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use InvalidArgumentException;

final readonly class OAuthAuthorizationServerMetadata
{
    /**
     * @param list<string> $grantTypesSupported
     * @param list<string> $responseTypesSupported
     * @param list<string> $scopesSupported
     * @param list<string> $tokenEndpointAuthMethodsSupported
     * @param list<string> $codeChallengeMethodsSupported
     */
    public function __construct(
        private string $issuer,
        private string $authorizationEndpoint,
        private string $tokenEndpoint,
        private array $grantTypesSupported = [],
        private array $responseTypesSupported = [],
        private array $scopesSupported = [],
        private array $tokenEndpointAuthMethodsSupported = [],
        private array $codeChallengeMethodsSupported = [],
        private ?string $jwksUri = null,
        private ?string $registrationEndpoint = null,
        private ?string $revocationEndpoint = null,
        private ?string $introspectionEndpoint = null,
        private ?string $pushedAuthorizationRequestEndpoint = null
    ) {
        if (
            trim($this->issuer) === ''
            || trim($this->authorizationEndpoint) === ''
            || trim($this->tokenEndpoint) === ''
        ) {
            throw new InvalidArgumentException(
                'OAuth authorization server metadata is invalid.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function authorizationEndpoint(): string
    {
        return $this->authorizationEndpoint;
    }

    public function tokenEndpoint(): string
    {
        return $this->tokenEndpoint;
    }

    /** @return list<string> */
    public function grantTypesSupported(): array
    {
        return $this->grantTypesSupported;
    }

    /** @return list<string> */
    public function responseTypesSupported(): array
    {
        return $this->responseTypesSupported;
    }

    /** @return list<string> */
    public function scopesSupported(): array
    {
        return $this->scopesSupported;
    }

    /** @return list<string> */
    public function tokenEndpointAuthMethodsSupported(): array
    {
        return $this->tokenEndpointAuthMethodsSupported;
    }

    /** @return list<string> */
    public function codeChallengeMethodsSupported(): array
    {
        return $this->codeChallengeMethodsSupported;
    }

    public function jwksUri(): ?string
    {
        return $this->jwksUri;
    }

    public function registrationEndpoint(): ?string
    {
        return $this->registrationEndpoint;
    }

    public function revocationEndpoint(): ?string
    {
        return $this->revocationEndpoint;
    }

    public function introspectionEndpoint(): ?string
    {
        return $this->introspectionEndpoint;
    }

    public function pushedAuthorizationRequestEndpoint(): ?string
    {
        return $this->pushedAuthorizationRequestEndpoint;
    }
}
