<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class ConfigurationTypeMismatchException extends ConfigurationException
{
    public static function forKey(
        string $key,
        string $expected,
        mixed $actual,
    ): self {
        return new self(sprintf(
            'Configuration key "%s" must contain %s; %s found.',
            $key,
            $expected,
            get_debug_type($actual),
        ));
    }
}
