<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

final readonly class TrustedDevicePolicyDecision
{
    private function __construct(
        private bool $trusted,
        private bool $mfaMayBeSkipped,
        private string $reason
    ) {
    }

    public static function trustedWithoutMfaBypass(string $reason): self
    {
        return new self(true, false, $reason);
    }

    public static function trustedWithExplicitMfaBypass(string $reason): self
    {
        return new self(true, true, $reason);
    }

    public static function rejected(string $reason): self
    {
        return new self(false, false, $reason);
    }

    public function isTrusted(): bool
    {
        return $this->trusted;
    }

    public function mfaMayBeSkipped(): bool
    {
        return $this->mfaMayBeSkipped;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
