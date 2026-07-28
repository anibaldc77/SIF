<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Exceptions;

final class InvalidLogLevelException extends LoggingException
{
    public static function forValue(string $value): self
    {
        return new self(sprintf('Invalid log level "%s".', $value));
    }
}
