<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Exceptions;

final class InvalidEnvironmentKeyException extends EnvironmentException
{
    public static function empty(): self
    {
        return new self('Environment variable key cannot be empty.');
    }
}
