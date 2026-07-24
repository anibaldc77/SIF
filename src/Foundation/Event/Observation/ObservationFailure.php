<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use DateTimeImmutable;
use JsonSerializable;

/** Immutable diagnostic for an isolated event-observation failure. */
final readonly class ObservationFailure implements JsonSerializable
{
    public function __construct(
        private object $event,
        private \Throwable $cause,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }

    public function event(): object
    {
        return $this->event;
    }

    public function eventType(): string
    {
        return $this->event::class;
    }

    public function cause(): \Throwable
    {
        return $this->cause;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /** @return array{event_type: class-string, cause_type: class-string<\Throwable>, message: string, occurred_at: string} */
    public function jsonSerialize(): array
    {
        return [
            'event_type' => $this->event::class,
            'cause_type' => $this->cause::class,
            'message' => $this->cause->getMessage(),
            'occurred_at' => $this->occurredAt->format(DATE_ATOM),
        ];
    }
}
