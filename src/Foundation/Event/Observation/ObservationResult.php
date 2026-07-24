<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

/** Immutable result of a single isolated observation attempt. */
final readonly class ObservationResult
{
    private function __construct(
        private object $event,
        private ?ObservationFailure $failure,
    ) {
    }

    public static function success(object $event): self
    {
        return new self($event, null);
    }

    public static function fromFailure(ObservationFailure $failure): self
    {
        return new self($failure->event(), $failure);
    }

    public function event(): object
    {
        return $this->event;
    }

    public function succeeded(): bool
    {
        return $this->failure === null;
    }

    public function failed(): bool
    {
        return $this->failure !== null;
    }

    public function failure(): ?ObservationFailure
    {
        return $this->failure;
    }
}
