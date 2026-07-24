<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class ConfigurationSourceNotFoundException extends ConfigurationSourceException
{
    public static function forSource(string $source): self
    {
        return new self(sprintf('Configuration source "%s" does not exist.', $source));
    }
}
