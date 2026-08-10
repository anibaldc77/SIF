<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class SecurityEventToken
{
    /**
     * @param list<SecurityEvent> $events
     */
    public function __construct(
        private string $issuer,
        private string $tokenId,
        private DateTimeImmutable $issuedAt,
        private array $events
    ) {
        if (
            trim($this->issuer) === ''
            || trim($this->tokenId) === ''
            || $this->events === []
        ) {
            throw new InvalidArgumentException(
                'Security event token is invalid.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function tokenId(): string
    {
        return $this->tokenId;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    /**
     * @return list<SecurityEvent>
     */
    public function events(): array
    {
        return $this->events;
    }
}
