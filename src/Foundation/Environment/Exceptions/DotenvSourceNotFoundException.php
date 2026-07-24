<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Exceptions;

final class DotenvSourceNotFoundException extends DotenvException
{
    public static function forPath(string $path): self
    {
        return new self(sprintf('Environment source "%s" was not found.', $path));
    }
}
