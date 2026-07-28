<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class InvalidEnvironmentConfigurationException extends ConfigurationSourceException
{
    public static function missingRequired(string $variable): self
    {
        return new self(sprintf('Required environment variable "%s" is not available.', $variable));
    }

    public static function invalidValue(string $variable, string $expected): self
    {
        return new self(sprintf('Environment variable "%s" cannot be parsed as %s.', $variable, $expected));
    }
}
