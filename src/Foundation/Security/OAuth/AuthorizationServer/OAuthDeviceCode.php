<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OAuthDeviceCode
{
    public function __construct(
        private string $value,
        private OAuthClientId $clientId,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private int $pollIntervalSeconds
    ) {
        if (
            trim($this->value) === ''
            || $this->expiresAt <= $this->issuedAt
            || $this->pollIntervalSeconds < 1
        ) {
            throw new InvalidArgumentException(
                'OAuth device code is invalid.'
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

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function pollIntervalSeconds(): int
    {
        return $this->pollIntervalSeconds;
    }

    public function expiredAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->expiresAt;
    }
}
