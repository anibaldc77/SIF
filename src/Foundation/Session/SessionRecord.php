<?php

declare(strict_types=1);

namespace Sif\Foundation\Session;

use DateTimeImmutable;

final readonly class SessionRecord
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $flashData
     */
    public function __construct(
        private SessionId $id,
        private array $data,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $lastAccessedAt,
        private int $version = 1,
        private array $flashData = [],
        private ?DateTimeImmutable $lastRegeneratedAt = null,
    ) {
    }

    public function id(): SessionId { return $this->id; }
    /** @return array<string, mixed> */
    public function data(): array { return $this->data; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function lastAccessedAt(): DateTimeImmutable { return $this->lastAccessedAt; }
    public function version(): int { return $this->version; }
    /** @return array<string, mixed> */
    public function flashData(): array { return $this->flashData; }
    public function lastRegeneratedAt(): DateTimeImmutable { return $this->lastRegeneratedAt ?? $this->createdAt; }

    public function expiredAt(DateTimeImmutable $now, SessionPolicy $policy): bool
    {
        return $now->getTimestamp() >= $this->createdAt->getTimestamp() + $policy->absoluteLifetimeSeconds()
            || $now->getTimestamp() >= $this->lastAccessedAt->getTimestamp() + $policy->idleLifetimeSeconds();
    }
}
