<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use InvalidArgumentException;

final class InvalidEventTypeException extends InvalidArgumentException
{
    public static function forType(string $eventType): self
    {
        return new self(sprintf('Event type "%s" must be an existing class or interface.', $eventType));
    }
}
