<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Exceptions;

final class UnreadableDotenvSourceException extends DotenvException
{
    public static function forPath(string $path): self
    {
        return new self(sprintf('Environment source "%s" is not readable.', $path));
    }
}
