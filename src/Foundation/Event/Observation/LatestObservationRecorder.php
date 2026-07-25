<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\EventObserverInterface;

/** Records the latest isolated observation result while preserving delegation. */
final class LatestObservationRecorder implements EventObserverInterface
{
    private ?ObservationResult $latest = null;

    public function __construct(private EventObserverInterface $delegate)
    {
    }

    public function observe(object $event): ObservationResult
    {
        try {
            $result = $this->delegate->observe($event);
        } catch (\Throwable $cause) {
            $result = ObservationResult::fromFailure(
                new ObservationFailure($event, $cause),
            );
        }

        $this->latest = $result;

        return $result;
    }

    public function latest(): ?ObservationResult
    {
        return $this->latest;
    }

    public function hasResult(): bool
    {
        return $this->latest !== null;
    }

    public function clear(): void
    {
        $this->latest = null;
    }
}
