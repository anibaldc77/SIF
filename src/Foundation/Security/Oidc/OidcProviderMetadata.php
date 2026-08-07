<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class OidcProviderMetadata
{
    /**
     * @param list<string> $responseTypesSupported
     * @param list<string> $subjectTypesSupported
     * @param list<string> $idTokenSigningAlgorithmsSupported
     */
    public function __construct(
        private string $issuer,
        private string $authorizationEndpoint,
        private string $tokenEndpoint,
        private string $jwksUri,
        private array $responseTypesSupported,
        private array $subjectTypesSupported,
        private array $idTokenSigningAlgorithmsSupported,
        private ?string $userInfoEndpoint = null
    ) {
        foreach ([
            'issuer' => $this->issuer,
            'authorization_endpoint' => $this->authorizationEndpoint,
            'token_endpoint' => $this->tokenEndpoint,
            'jwks_uri' => $this->jwksUri,
        ] as $name => $value) {
            if ($value === '' || strlen($value) > 2048) {
                throw new InvalidArgumentException(
                    sprintf('OIDC %s value is invalid.', $name)
                );
            }
        }

        if ($this->idTokenSigningAlgorithmsSupported === []) {
            throw new InvalidArgumentException(
                'OIDC provider must advertise at least one ID token signing algorithm.'
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

    public function jwksUri(): string
    {
        return $this->jwksUri;
    }

    /** @return list<string> */
    public function responseTypesSupported(): array
    {
        return $this->responseTypesSupported;
    }

    /** @return list<string> */
    public function subjectTypesSupported(): array
    {
        return $this->subjectTypesSupported;
    }

    /** @return list<string> */
    public function idTokenSigningAlgorithmsSupported(): array
    {
        return $this->idTokenSigningAlgorithmsSupported;
    }

    public function userInfoEndpoint(): ?string
    {
        return $this->userInfoEndpoint;
    }
}
