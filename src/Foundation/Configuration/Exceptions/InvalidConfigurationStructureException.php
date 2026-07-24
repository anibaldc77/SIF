<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class InvalidConfigurationStructureException extends ConfigurationException
{
    public static function cannotDescend(string $key, string $segment): self
    {
        return new self(sprintf(
            'Configuration key "%s" cannot be written because segment "%s" is not an array.',
            $key,
            $segment,
        ));
    }
}
