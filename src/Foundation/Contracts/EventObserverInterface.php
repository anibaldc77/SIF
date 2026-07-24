<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Event\Observation\ObservationResult;

/** Isolated, side-effect-bounded observation of a dispatched event. */
interface EventObserverInterface
{
    public function observe(object $event): ObservationResult;
}
