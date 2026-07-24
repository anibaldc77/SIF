<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\EventObserverInterface;

/** No-op observer used when observation is intentionally disabled. */
final readonly class NullEventObserver implements EventObserverInterface
{
    public function observe(object $event): ObservationResult
    {
        return ObservationResult::success($event);
    }
}
