<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthClient
{
    /**
     * @param list<OAuthRedirectUri> $redirectUris
     * @param list<OAuthScope> $allowedScopes
     */
    public function __construct(
        private OAuthClientId $id,
        private string $name,
        private bool $confidential,
        private array $redirectUris,
        private array $allowedScopes
    ) {
    }

    public function id(): OAuthClientId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function confidential(): bool
    {
        return $this->confidential;
    }

    /**
     * @return list<OAuthRedirectUri>
     */
    public function redirectUris(): array
    {
        return $this->redirectUris;
    }

    /**
     * @return list<OAuthScope>
     */
    public function allowedScopes(): array
    {
        return $this->allowedScopes;
    }
}
