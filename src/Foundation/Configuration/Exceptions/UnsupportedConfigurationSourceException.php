<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class UnsupportedConfigurationSourceException extends ConfigurationSourceException
{
    public static function forSource(string $source): self
    {
        return new self(sprintf(
            'No configuration loader supports source "%s".',
            $source,
        ));
    }
}
