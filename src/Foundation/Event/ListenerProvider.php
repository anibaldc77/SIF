<?php

declare(strict_types=1);

namespace Sif\Foundation\Event;

use Closure;
use Sif\Foundation\Contracts\EventSubscriberInterface;
use Sif\Foundation\Contracts\ListenerProviderInterface;
use Sif\Foundation\Exceptions\InvalidEventSubscriberException;
use Sif\Foundation\Exceptions\InvalidEventTypeException;

/** In-memory listener registry with deterministic priority and insertion ordering. */
final class ListenerProvider implements ListenerProviderInterface
{
    /** @var list<ListenerDefinition> */
    private array $definitions = [];

    private int $sequence = 0;

    /**
     * @param class-string $eventType
     * @param callable(object): void $listener
     */
    public function add(string $eventType, callable $listener, int $priority = 0): self
    {
        if (!class_exists($eventType) && !interface_exists($eventType)) {
            throw InvalidEventTypeException::forType($eventType);
        }

        $this->definitions[] = new ListenerDefinition(
            $eventType,
            Closure::fromCallable($listener),
            $priority,
            $this->sequence++,
        );

        return $this;
    }

    public function subscribe(EventSubscriberInterface $subscriber): self
    {
        foreach ($subscriber::subscribedEvents() as $eventType => $subscription) {
            [$method, $priority] = is_array($subscription)
                ? $subscription
                : [$subscription, 0];

            $listener = [$subscriber, $method];
            if (!is_callable($listener)) {
                throw InvalidEventSubscriberException::forMethod($subscriber, $eventType, $method);
            }

            /** @var callable(object): void $listener */
            $this->add($eventType, $listener, $priority);
        }

        return $this;
    }

    public function listenersFor(object $event): iterable
    {
        $matching = array_values(array_filter(
            $this->definitions,
            static fn (ListenerDefinition $definition): bool => is_a($event, $definition->eventType()),
        ));

        usort(
            $matching,
            static fn (ListenerDefinition $left, ListenerDefinition $right): int =>
                ($right->priority() <=> $left->priority())
                ?: ($left->sequence() <=> $right->sequence()),
        );

        foreach ($matching as $definition) {
            yield $definition->listener();
        }
    }

    public function count(): int
    {
        return count($this->definitions);
    }
}
