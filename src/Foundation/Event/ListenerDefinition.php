<?php

declare(strict_types=1);

namespace Sif\Foundation\Event;

use Closure;

/** @internal Ordered listener registration. */
final readonly class ListenerDefinition
{
    /** @param Closure(object): void $listener */
    public function __construct(
        private string $eventType,
        private Closure $listener,
        private int $priority,
        private int $sequence,
    ) {
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    /** @return Closure(object): void */
    public function listener(): Closure
    {
        return $this->listener;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function sequence(): int
    {
        return $this->sequence;
    }
}
