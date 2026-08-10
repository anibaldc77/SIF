<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class SharedSignalsStream
{
    /**
     * @param list<string> $eventTypes
     */
    public function __construct(
        private string $streamId,
        private string $issuer,
        private string $audience,
        private array $eventTypes = []
    ) {
        if (
            trim($this->streamId) === ''
            || trim($this->issuer) === ''
            || trim($this->audience) === ''
        ) {
            throw new InvalidArgumentException(
                'Shared signals stream is invalid.'
            );
        }
    }

    public function streamId(): string
    {
        return $this->streamId;
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    /**
     * @return list<string>
     */
    public function eventTypes(): array
    {
        return $this->eventTypes;
    }
}
