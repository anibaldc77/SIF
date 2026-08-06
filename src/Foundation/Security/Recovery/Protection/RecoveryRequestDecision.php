<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\Protection;

use DateTimeImmutable;

final readonly class RecoveryRequestDecision
{
    private function __construct(
        private bool $allowed,
        private ?DateTimeImmutable $retryAt
    ) {
    }

    public static function allow(): self
    {
        return new self(true, null);
    }

    public static function blockUntil(DateTimeImmutable $retryAt): self
    {
        return new self(false, $retryAt);
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function retryAt(): ?DateTimeImmutable
    {
        return $this->retryAt;
    }
}
