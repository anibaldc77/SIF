<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OAuthAccessToken
{
    /**
     * @param list<OAuthScope> $scopes
     */
    public function __construct(
        private string $value,
        private OAuthClientId $clientId,
        private array $scopes,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt
    ) {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'OAuth access token is invalid.'
            );
        }

        if ($this->expiresAt <= $this->issuedAt) {
            throw new InvalidArgumentException(
                'OAuth access token expiration is invalid.'
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

    /**
     * @return list<OAuthScope>
     */
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

    public function expiredAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->expiresAt;
    }
}
