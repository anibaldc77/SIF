<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class InvalidConfigurationKeyException extends ConfigurationException
{
    public static function empty(): self
    {
        return new self('A configuration key cannot be empty.');
    }

    public static function emptySegment(string $key): self
    {
        return new self(sprintf(
            'Configuration key "%s" contains an empty path segment.',
            $key,
        ));
    }
}
