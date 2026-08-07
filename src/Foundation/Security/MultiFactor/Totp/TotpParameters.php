<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final readonly class TotpParameters
{
    public function __construct(
        private TotpHashAlgorithm $algorithm,
        private int $digits = 6,
        private int $periodSeconds = 30,
        private int $allowedPastWindows = 1,
        private int $allowedFutureWindows = 1
    ) {
        if ($digits < 6 || $digits > 8) {
            throw new InvalidTotpConfigurationException('TOTP digits must be between 6 and 8.');
        }

        if ($periodSeconds < 15 || $periodSeconds > 300) {
            throw new InvalidTotpConfigurationException('TOTP period must be between 15 and 300 seconds.');
        }

        if ($allowedPastWindows < 0 || $allowedPastWindows > 10) {
            throw new InvalidTotpConfigurationException('TOTP past window allowance must be between 0 and 10.');
        }

        if ($allowedFutureWindows < 0 || $allowedFutureWindows > 10) {
            throw new InvalidTotpConfigurationException('TOTP future window allowance must be between 0 and 10.');
        }
    }

    public static function rfc6238(): self
    {
        return new self(TotpHashAlgorithm::sha1());
    }

    public function algorithm(): TotpHashAlgorithm
    {
        return $this->algorithm;
    }

    public function digits(): int
    {
        return $this->digits;
    }

    public function periodSeconds(): int
    {
        return $this->periodSeconds;
    }

    public function allowedPastWindows(): int
    {
        return $this->allowedPastWindows;
    }

    public function allowedFutureWindows(): int
    {
        return $this->allowedFutureWindows;
    }

    /** @return array{algorithm: string, digits: int, period_seconds: int, allowed_past_windows: int, allowed_future_windows: int} */
    public function snapshot(): array
    {
        return [
            'algorithm' => $this->algorithm->value(),
            'digits' => $this->digits,
            'period_seconds' => $this->periodSeconds,
            'allowed_past_windows' => $this->allowedPastWindows,
            'allowed_future_windows' => $this->allowedFutureWindows,
        ];
    }
}
