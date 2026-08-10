<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

final readonly class SharedSignalsDeliveryResult
{
    public function __construct(
        private bool $accepted,
        private bool $retryable,
        private ?string $reason = null
    ) {
    }

    public function accepted(): bool
    {
        return $this->accepted;
    }

    public function retryable(): bool
    {
        return $this->retryable;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }
}
