<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use DateTimeImmutable;

final readonly class OAuthTokenStatus
{
    public function __construct(
        private bool $revoked,
        private DateTimeImmutable $expiresAt
    ) {
    }

    public function revoked(): bool
    {
        return $this->revoked;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function activeAt(DateTimeImmutable $instant): bool
    {
        return !$this->revoked
            && $instant < $this->expiresAt;
    }
}
