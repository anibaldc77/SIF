<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class ConfigurationNotFoundException extends ConfigurationException
{
    public static function forKey(string $key): self
    {
        return new self(sprintf('Configuration key "%s" is not defined.', $key));
    }
}
