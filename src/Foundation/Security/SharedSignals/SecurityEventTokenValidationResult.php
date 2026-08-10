<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

final readonly class SecurityEventTokenValidationResult
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        private SecurityEventToken $token,
        private bool $issuerValid,
        private bool $audienceValid,
        private bool $timeValid,
        private bool $replaySafe,
        private array $warnings = []
    ) {
    }

    public function token(): SecurityEventToken
    {
        return $this->token;
    }

    public function issuerValid(): bool
    {
        return $this->issuerValid;
    }

    public function audienceValid(): bool
    {
        return $this->audienceValid;
    }

    public function timeValid(): bool
    {
        return $this->timeValid;
    }

    public function replaySafe(): bool
    {
        return $this->replaySafe;
    }

    public function valid(): bool
    {
        return $this->issuerValid
            && $this->audienceValid
            && $this->timeValid
            && $this->replaySafe;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
