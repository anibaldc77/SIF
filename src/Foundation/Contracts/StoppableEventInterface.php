<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Indicates whether an event requests that listener propagation stop. */
interface StoppableEventInterface
{
    public function isPropagationStopped(): bool;
}
