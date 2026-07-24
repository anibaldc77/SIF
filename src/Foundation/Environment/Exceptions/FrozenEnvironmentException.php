<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Exceptions;

final class FrozenEnvironmentException extends EnvironmentException
{
    public static function mutationAttempted(): self
    {
        return new self('Environment repository is frozen and cannot be modified.');
    }
}
