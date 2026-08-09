<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OAuthAuthorizationCode
{
    /**
     * @param list<OAuthScope> $scopes
     */
    public function __construct(
        private string $value,
        private OAuthClientId $clientId,
        private OAuthRedirectUri $redirectUri,
        private array $scopes,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private ?string $codeChallenge = null,
        private ?string $codeChallengeMethod = null
    ) {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'OAuth authorization code is invalid.'
            );
        }

        if ($this->expiresAt <= $this->issuedAt) {
            throw new InvalidArgumentException(
                'OAuth authorization code expiration is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function clientId(): OAuthClientId
    {
        return $this->clientId;
    }

    public function redirectUri(): OAuthRedirectUri
    {
        return $this->redirectUri;
    }

    /** @return list<OAuthScope> */
    public function scopes(): array
    {
        return $this->scopes;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function codeChallenge(): ?string
    {
        return $this->codeChallenge;
    }

    public function codeChallengeMethod(): ?string
    {
        return $this->codeChallengeMethod;
    }

    public function expiredAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->expiresAt;
    }
}
