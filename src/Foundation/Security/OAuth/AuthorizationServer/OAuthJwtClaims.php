<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OAuthJwtClaims
{
    /**
     * @param list<string> $audience
     * @param list<OAuthScope> $scopes
     */
    public function __construct(
        private string $issuer,
        private string $subject,
        private array $audience,
        private OAuthClientId $clientId,
        private array $scopes,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private string $tokenId
    ) {
        if (
            trim($this->issuer) === ''
            || trim($this->subject) === ''
            || trim($this->tokenId) === ''
            || $this->audience === []
        ) {
            throw new InvalidArgumentException(
                'OAuth JWT claims are invalid.'
            );
        }

        if ($this->expiresAt <= $this->issuedAt) {
            throw new InvalidArgumentException(
                'OAuth JWT claims expiration is invalid.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    /**
     * @return list<string>
     */
    public function audience(): array
    {
        return $this->audience;
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

    public function tokenId(): string
    {
        return $this->tokenId;
    }
}
