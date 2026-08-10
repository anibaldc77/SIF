<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class SharedSignalsStreamConfiguration
{
    /**
     * @param list<string> $eventTypes
     */
    public function __construct(
        private string $streamId,
        private string $deliveryMethod,
        private array $eventTypes,
        private bool $enabled = true
    ) {
        if (
            trim($this->streamId) === ''
            || trim($this->deliveryMethod) === ''
            || $this->eventTypes === []
        ) {
            throw new InvalidArgumentException(
                'Shared signals stream configuration is invalid.'
            );
        }
    }

    public function streamId(): string
    {
        return $this->streamId;
    }

    public function deliveryMethod(): string
    {
        return $this->deliveryMethod;
    }

    /**
     * @return list<string>
     */
    public function eventTypes(): array
    {
        return $this->eventTypes;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
