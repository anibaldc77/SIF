<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password;

final readonly class PasswordVerificationResult
{
    private function __construct(
        private bool $verified,
        private bool $rehashRequired
    ) {
    }

    public static function verified(bool $rehashRequired = false): self
    {
        return new self(true, $rehashRequired);
    }

    public static function rejected(): self
    {
        return new self(false, false);
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function requiresRehash(): bool
    {
        return $this->rehashRequired;
    }
}
