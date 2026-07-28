<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class UnsupportedConfigurationValueException extends ConfigurationException
{
    public static function forValue(mixed $value): self
    {
        return new self(sprintf(
            'Unsupported configuration value of type "%s".',
            get_debug_type($value),
        ));
    }
}
