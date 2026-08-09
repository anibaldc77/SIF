<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthClientCredentialsGrant
{
    /**
     * @param list<OAuthScope> $scopes
     */
    public function __construct(
        private OAuthClientId $clientId,
        private array $scopes
    ) {
    }

    public function clientId(): OAuthClientId
    {
        return $this->clientId;
    }

    /**
     * @return list<OAuthScope>
     */
    public function scopes(): array
    {
        return $this->scopes;
    }
}
