<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final readonly class TotpVerificationResult
{
    private function __construct(
        private bool $verified,
        private ?int $matchedCounter
    ) {
        if ($verified && $matchedCounter === null) {
            throw new InvalidTotpConfigurationException('Verified TOTP result requires the matched counter.');
        }

        if (!$verified && $matchedCounter !== null) {
            throw new InvalidTotpConfigurationException('Rejected TOTP result cannot expose a matched counter.');
        }
    }

    public static function verified(int $matchedCounter): self
    {
        if ($matchedCounter < 0) {
            throw new InvalidTotpConfigurationException('TOTP counter cannot be negative.');
        }

        return new self(true, $matchedCounter);
    }

    public static function rejected(): self
    {
        return new self(false, null);
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function matchedCounter(): ?int
    {
        return $this->matchedCounter;
    }
}
