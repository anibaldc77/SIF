<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use DateTimeImmutable;

final readonly class OAuthTokenIntrospection
{
    /**
     * @param list<OAuthScope> $scopes
     */
    public function __construct(
        private bool $active,
        private OAuthClientId $clientId,
        private array $scopes,
        private DateTimeImmutable $expiresAt,
        private ?string $subject = null
    ) {
    }

    public function active(): bool
    {
        return $this->active;
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

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function subject(): ?string
    {
        return $this->subject;
    }
}
