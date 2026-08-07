<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Policy;

final readonly class CompositeAuthorizationEvaluation
{
    public function __construct(
        private bool $satisfied,
        private string $reason
    ) {
    }

    public static function satisfied(string $reason): self
    {
        return new self(true, $reason);
    }

    public static function rejected(string $reason): self
    {
        return new self(false, $reason);
    }

    public function isSatisfied(): bool
    {
        return $this->satisfied;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
