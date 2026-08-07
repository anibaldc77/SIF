<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Logout;

use InvalidArgumentException;
use Sif\Foundation\Security\Oidc\OidcIdToken;

final readonly class OidcLogoutRequest
{
    public function __construct(
        private string $endSessionEndpoint,
        private ?OidcIdToken $idTokenHint = null,
        private ?string $postLogoutRedirectUri = null
    ) {
        if (
            $this->endSessionEndpoint === ''
            || strlen($this->endSessionEndpoint) > 4096
        ) {
            throw new InvalidArgumentException(
                'OIDC end-session endpoint is invalid.'
            );
        }
    }

    public function endSessionEndpoint(): string
    {
        return $this->endSessionEndpoint;
    }

    public function idTokenHint(): ?OidcIdToken
    {
        return $this->idTokenHint;
    }

    public function postLogoutRedirectUri(): ?string
    {
        return $this->postLogoutRedirectUri;
    }
}
