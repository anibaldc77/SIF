<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use DateTimeImmutable;

final readonly class SharedSignalsStreamStatus
{
    public function __construct(
        private string $streamId,
        private bool $enabled,
        private ?DateTimeImmutable $lastSuccessfulDeliveryAt = null,
        private ?DateTimeImmutable $lastFailedDeliveryAt = null
    ) {
    }

    public function streamId(): string
    {
        return $this->streamId;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function lastSuccessfulDeliveryAt(): ?DateTimeImmutable
    {
        return $this->lastSuccessfulDeliveryAt;
    }

    public function lastFailedDeliveryAt(): ?DateTimeImmutable
    {
        return $this->lastFailedDeliveryAt;
    }
}
