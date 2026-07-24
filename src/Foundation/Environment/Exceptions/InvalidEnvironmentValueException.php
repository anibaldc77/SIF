<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Exceptions;

final class InvalidEnvironmentValueException extends EnvironmentException
{
    public static function forKey(string $key): self
    {
        return new self(sprintf('Environment variable "%s" must contain a scalar or null value.', $key));
    }
}
