<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Exceptions;

final class InvalidDotenvSyntaxException extends DotenvException
{
    public static function atLine(int $line, string $reason): self
    {
        return new self(sprintf('Invalid .env syntax at line %d: %s', $line, $reason));
    }
}
