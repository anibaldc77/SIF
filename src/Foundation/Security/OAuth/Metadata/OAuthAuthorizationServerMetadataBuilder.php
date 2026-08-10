<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

final class OAuthAuthorizationServerMetadataBuilder
{
    /** @var list<string> */
    private array $grantTypesSupported = [];

    /** @var list<string> */
    private array $responseTypesSupported = [];

    /** @var list<string> */
    private array $scopesSupported = [];

    /** @var list<string> */
    private array $tokenEndpointAuthMethodsSupported = [];

    /** @var list<string> */
    private array $codeChallengeMethodsSupported = [];

    private ?string $jwksUri = null;
    private ?string $registrationEndpoint = null;
    private ?string $revocationEndpoint = null;
    private ?string $introspectionEndpoint = null;
    private ?string $pushedAuthorizationRequestEndpoint = null;

    public function __construct(
        private readonly string $issuer,
        private readonly string $authorizationEndpoint,
        private readonly string $tokenEndpoint
    ) {
    }

    /** @param list<string> $values */
    public function withGrantTypes(array $values): self
    {
        $clone = clone $this;
        $clone->grantTypesSupported = $values;

        return $clone;
    }

    /** @param list<string> $values */
    public function withResponseTypes(array $values): self
    {
        $clone = clone $this;
        $clone->responseTypesSupported = $values;

        return $clone;
    }

    /** @param list<string> $values */
    public function withScopes(array $values): self
    {
        $clone = clone $this;
        $clone->scopesSupported = $values;

        return $clone;
    }

    /** @param list<string> $values */
    public function withTokenEndpointAuthMethods(array $values): self
    {
        $clone = clone $this;
        $clone->tokenEndpointAuthMethodsSupported = $values;

        return $clone;
    }

    /** @param list<string> $values */
    public function withCodeChallengeMethods(array $values): self
    {
        $clone = clone $this;
        $clone->codeChallengeMethodsSupported = $values;

        return $clone;
    }

    public function withJwksUri(?string $value): self
    {
        $clone = clone $this;
        $clone->jwksUri = $value;

        return $clone;
    }

    public function withRegistrationEndpoint(?string $value): self
    {
        $clone = clone $this;
        $clone->registrationEndpoint = $value;

        return $clone;
    }

    public function withRevocationEndpoint(?string $value): self
    {
        $clone = clone $this;
        $clone->revocationEndpoint = $value;

        return $clone;
    }

    public function withIntrospectionEndpoint(?string $value): self
    {
        $clone = clone $this;
        $clone->introspectionEndpoint = $value;

        return $clone;
    }

    public function withPushedAuthorizationRequestEndpoint(?string $value): self
    {
        $clone = clone $this;
        $clone->pushedAuthorizationRequestEndpoint = $value;

        return $clone;
    }

    public function build(): OAuthAuthorizationServerMetadata
    {
        return new OAuthAuthorizationServerMetadata(
            $this->issuer,
            $this->authorizationEndpoint,
            $this->tokenEndpoint,
            $this->grantTypesSupported,
            $this->responseTypesSupported,
            $this->scopesSupported,
            $this->tokenEndpointAuthMethodsSupported,
            $this->codeChallengeMethodsSupported,
            $this->jwksUri,
            $this->registrationEndpoint,
            $this->revocationEndpoint,
            $this->introspectionEndpoint,
            $this->pushedAuthorizationRequestEndpoint
        );
    }
}
