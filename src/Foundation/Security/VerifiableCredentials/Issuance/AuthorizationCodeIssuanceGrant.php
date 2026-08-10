<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class AuthorizationCodeIssuanceGrant
{
    public function __construct(
        private string $authorizationCode,
        private string $clientId,
        private ?string $redirectUri = null,
        private ?string $codeVerifier = null
    ) {
        if (
            trim($this->authorizationCode) === ''
            || trim($this->clientId) === ''
        ) {
            throw new InvalidArgumentException(
                'Authorization Code issuance grant is invalid.'
            );
        }
    }

    public function authorizationCode(): string
    {
        return $this->authorizationCode;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function redirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function codeVerifier(): ?string
    {
        return $this->codeVerifier;
    }
}
