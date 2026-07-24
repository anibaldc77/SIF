<?php

declare(strict_types=1);

namespace Sif\Foundation\Event;

use Sif\Foundation\Contracts\EventDispatcherInterface;
use Sif\Foundation\Contracts\ListenerProviderInterface;
use Sif\Foundation\Contracts\StoppableEventInterface;

/** Synchronous event dispatcher. Listener exceptions propagate unchanged. */
final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(private ListenerProviderInterface $listenerProvider)
    {
    }

    public function dispatch(object $event): object
    {
        foreach ($this->listenerProvider->listenersFor($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }

            $listener($event);
        }

        return $event;
    }
}
