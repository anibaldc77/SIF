<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OAuthRefreshToken
{
    /**
     * @param list<OAuthScope> $scopes
     */
    public function __construct(
        private string $value,
        private OAuthClientId $clientId,
        private OAuthRefreshTokenFamilyId $familyId,
        private array $scopes,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private ?string $replacesToken = null
    ) {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'OAuth refresh token is invalid.'
            );
        }

        if ($this->expiresAt <= $this->issuedAt) {
            throw new InvalidArgumentException(
                'OAuth refresh token expiration is invalid.'
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

    public function familyId(): OAuthRefreshTokenFamilyId
    {
        return $this->familyId;
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

    public function replacesToken(): ?string
    {
        return $this->replacesToken;
    }

    public function expiredAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->expiresAt;
    }
}
