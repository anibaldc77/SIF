<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

final readonly class TotpFactorVerificationResult
{
    private function __construct(private bool $verified)
    {
    }

    public static function verified(): self
    {
        return new self(true);
    }

    public static function rejected(): self
    {
        return new self(false);
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }
}
