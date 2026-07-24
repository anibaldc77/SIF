<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use InvalidArgumentException;

final class InvalidEventSubscriberException extends InvalidArgumentException
{
    public static function forMethod(object $subscriber, string $eventType, string $method): self
    {
        return new self(sprintf(
            'Subscriber "%s" method "%s" for event "%s" is not callable.',
            $subscriber::class,
            $method,
            $eventType,
        ));
    }
}
