<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Exceptions;

final class UnresolvedEnvironmentVariableException extends DotenvException
{
    public static function forVariable(string $variable, int $line): self
    {
        return new self(sprintf(
            'Environment variable "%s" referenced at line %d could not be resolved.',
            $variable,
            $line,
        ));
    }
}
