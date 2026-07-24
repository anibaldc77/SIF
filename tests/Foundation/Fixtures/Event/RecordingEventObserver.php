<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Event;

use Sif\Foundation\Contracts\EventObserverInterface;
use Sif\Foundation\Event\Observation\ObservationResult;

final class RecordingEventObserver implements EventObserverInterface
{
    /** @var list<object> */
    public array $events = [];

    public function __construct(private ?\Throwable $failure = null)
    {
    }

    public function observe(object $event): ObservationResult
    {
        $this->events[] = $event;

        if ($this->failure !== null) {
            throw $this->failure;
        }

        return ObservationResult::success($event);
    }
}
