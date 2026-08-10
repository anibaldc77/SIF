<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

final class OAuthProtectedResourceMetadataBuilder
{
    /** @var list<string> */
    private array $scopesSupported = [];

    private ?string $jwksUri = null;

    /** @var list<string> */
    private array $bearerMethodsSupported = [];

    /** @var list<string> */
    private array $resourceSigningAlgValuesSupported = [];

    private ?string $resourceName = null;
    private ?string $resourceDocumentation = null;
    private ?string $resourcePolicyUri = null;
    private ?string $resourceTosUri = null;
    private bool $tlsClientCertificateBoundAccessTokens = false;

    /** @var list<string> */
    private array $authorizationDetailsTypesSupported = [];

    /** @var list<string> */
    private array $dpopSigningAlgValuesSupported = [];

    private bool $dpopBoundAccessTokensRequired = false;
    private ?string $signedMetadata = null;

    /**
     * @param list<string> $authorizationServers
     */
    public function __construct(
        private readonly string $resource,
        private readonly array $authorizationServers
    ) {
    }

    /** @param list<string> $values */
    public function withScopes(array $values): self
    {
        $clone = clone $this;
        $clone->scopesSupported = $values;

        return $clone;
    }

    public function withJwksUri(?string $value): self
    {
        $clone = clone $this;
        $clone->jwksUri = $value;

        return $clone;
    }

    /** @param list<string> $values */
    public function withBearerMethods(array $values): self
    {
        $clone = clone $this;
        $clone->bearerMethodsSupported = $values;

        return $clone;
    }

    /** @param list<string> $values */
    public function withResourceSigningAlgorithms(array $values): self
    {
        $clone = clone $this;
        $clone->resourceSigningAlgValuesSupported = $values;

        return $clone;
    }

    public function withResourceName(?string $value): self
    {
        $clone = clone $this;
        $clone->resourceName = $value;

        return $clone;
    }

    public function withResourceDocumentation(?string $value): self
    {
        $clone = clone $this;
        $clone->resourceDocumentation = $value;

        return $clone;
    }

    public function withResourcePolicyUri(?string $value): self
    {
        $clone = clone $this;
        $clone->resourcePolicyUri = $value;

        return $clone;
    }

    public function withResourceTosUri(?string $value): self
    {
        $clone = clone $this;
        $clone->resourceTosUri = $value;

        return $clone;
    }

    public function withTlsClientCertificateBoundAccessTokens(bool $value): self
    {
        $clone = clone $this;
        $clone->tlsClientCertificateBoundAccessTokens = $value;

        return $clone;
    }

    /** @param list<string> $values */
    public function withAuthorizationDetailsTypes(array $values): self
    {
        $clone = clone $this;
        $clone->authorizationDetailsTypesSupported = $values;

        return $clone;
    }

    /** @param list<string> $values */
    public function withDpopSigningAlgorithms(array $values): self
    {
        $clone = clone $this;
        $clone->dpopSigningAlgValuesSupported = $values;

        return $clone;
    }

    public function withDpopBoundAccessTokensRequired(bool $value): self
    {
        $clone = clone $this;
        $clone->dpopBoundAccessTokensRequired = $value;

        return $clone;
    }

    public function withSignedMetadata(?string $value): self
    {
        $clone = clone $this;
        $clone->signedMetadata = $value;

        return $clone;
    }

    public function build(): OAuthProtectedResourceMetadata
    {
        return new OAuthProtectedResourceMetadata(
            $this->resource,
            $this->authorizationServers,
            $this->scopesSupported,
            $this->jwksUri,
            $this->bearerMethodsSupported,
            $this->resourceSigningAlgValuesSupported,
            $this->resourceName,
            $this->resourceDocumentation,
            $this->resourcePolicyUri,
            $this->resourceTosUri,
            $this->tlsClientCertificateBoundAccessTokens,
            $this->authorizationDetailsTypesSupported,
            $this->dpopSigningAlgValuesSupported,
            $this->dpopBoundAccessTokensRequired,
            $this->signedMetadata
        );
    }
}
