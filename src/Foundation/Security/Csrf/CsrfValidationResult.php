<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Csrf;

final readonly class CsrfValidationResult
{
    private function __construct(
        private bool $valid,
        private ?CsrfFailureReason $reason = null,
    ) {
    }

    public static function valid(): self
    {
        return new self(true);
    }

    public static function invalid(CsrfFailureReason $reason): self
    {
        return new self(false, $reason);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function reason(): ?CsrfFailureReason
    {
        return $this->reason;
    }
}
