<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\EventObserverInterface;

/**
 * Observes an event through multiple observers in deterministic insertion order.
 *
 * Every observer is attempted. The first failure is retained as the aggregate
 * result while later observers continue to receive the same event.
 */
final readonly class CompositeEventObserver implements EventObserverInterface
{
    /** @var list<EventObserverInterface> */
    private array $observers;

    /** @param list<EventObserverInterface> $observers */
    public function __construct(array $observers)
    {
        $this->observers = array_values($observers);
    }

    public function observe(object $event): ObservationResult
    {
        $firstFailure = null;

        foreach ($this->observers as $observer) {
            try {
                $result = $observer->observe($event);
            } catch (\Throwable $cause) {
                $result = ObservationResult::fromFailure(
                    new ObservationFailure($event, $cause),
                );
            }

            if ($firstFailure === null && $result->failed()) {
                $firstFailure = $result->failure();
            }
        }

        if ($firstFailure !== null) {
            return ObservationResult::fromFailure($firstFailure);
        }

        return ObservationResult::success($event);
    }
}
