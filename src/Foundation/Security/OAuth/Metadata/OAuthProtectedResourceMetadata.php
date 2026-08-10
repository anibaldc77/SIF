<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use InvalidArgumentException;

final readonly class OAuthProtectedResourceMetadata
{
    /**
     * @param list<string> $authorizationServers
     * @param list<string> $scopesSupported
     * @param list<string> $bearerMethodsSupported
     * @param list<string> $resourceSigningAlgValuesSupported
     * @param list<string> $authorizationDetailsTypesSupported
     * @param list<string> $dpopSigningAlgValuesSupported
     */
    public function __construct(
        private string $resource,
        private array $authorizationServers,
        private array $scopesSupported = [],
        private ?string $jwksUri = null,
        private array $bearerMethodsSupported = [],
        private array $resourceSigningAlgValuesSupported = [],
        private ?string $resourceName = null,
        private ?string $resourceDocumentation = null,
        private ?string $resourcePolicyUri = null,
        private ?string $resourceTosUri = null,
        private bool $tlsClientCertificateBoundAccessTokens = false,
        private array $authorizationDetailsTypesSupported = [],
        private array $dpopSigningAlgValuesSupported = [],
        private bool $dpopBoundAccessTokensRequired = false,
        private ?string $signedMetadata = null
    ) {
        if (
            trim($this->resource) === ''
            || $this->authorizationServers === []
        ) {
            throw new InvalidArgumentException(
                'OAuth protected resource metadata is invalid.'
            );
        }
    }

    public function resource(): string
    {
        return $this->resource;
    }

    /** @return list<string> */
    public function authorizationServers(): array
    {
        return $this->authorizationServers;
    }

    /** @return list<string> */
    public function scopesSupported(): array
    {
        return $this->scopesSupported;
    }

    public function jwksUri(): ?string
    {
        return $this->jwksUri;
    }

    /** @return list<string> */
    public function bearerMethodsSupported(): array
    {
        return $this->bearerMethodsSupported;
    }

    /** @return list<string> */
    public function resourceSigningAlgValuesSupported(): array
    {
        return $this->resourceSigningAlgValuesSupported;
    }

    public function resourceName(): ?string
    {
        return $this->resourceName;
    }

    public function resourceDocumentation(): ?string
    {
        return $this->resourceDocumentation;
    }

    public function resourcePolicyUri(): ?string
    {
        return $this->resourcePolicyUri;
    }

    public function resourceTosUri(): ?string
    {
        return $this->resourceTosUri;
    }

    public function tlsClientCertificateBoundAccessTokens(): bool
    {
        return $this->tlsClientCertificateBoundAccessTokens;
    }

    /** @return list<string> */
    public function authorizationDetailsTypesSupported(): array
    {
        return $this->authorizationDetailsTypesSupported;
    }

    /** @return list<string> */
    public function dpopSigningAlgValuesSupported(): array
    {
        return $this->dpopSigningAlgValuesSupported;
    }

    public function dpopBoundAccessTokensRequired(): bool
    {
        return $this->dpopBoundAccessTokensRequired;
    }

    public function signedMetadata(): ?string
    {
        return $this->signedMetadata;
    }
}
