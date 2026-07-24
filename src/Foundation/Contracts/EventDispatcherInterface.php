<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Dispatches an event synchronously to the listeners provided for it. */
interface EventDispatcherInterface
{
    /**
     * Dispatches the event and returns the same instance.
     *
     * @template TEvent of object
     *
     * @param TEvent $event
     *
     * @return TEvent
     */
    public function dispatch(object $event): object;
}
