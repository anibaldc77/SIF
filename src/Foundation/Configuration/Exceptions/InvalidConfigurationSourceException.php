<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

use Throwable;

final class InvalidConfigurationSourceException extends ConfigurationSourceException
{
    public static function nonArrayResult(string $source): self
    {
        return new self(sprintf(
            'Configuration source "%s" must produce an array.',
            $source,
        ));
    }

    public static function cannotLoad(string $source, Throwable $cause): self
    {
        return new self(sprintf(
            'Configuration source "%s" could not be loaded: %s',
            $source,
            $cause->getMessage(),
        ), 0, $cause);
    }

    public static function invalidJson(string $source, Throwable $cause): self
    {
        return new self(sprintf(
            'Configuration source "%s" contains invalid JSON: %s',
            $source,
            $cause->getMessage(),
        ), 0, $cause);
    }
}
