<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata\Exception;

use RuntimeException;

final class MetadataReadException extends RuntimeException
{
    public static function unreadable(string $path): self
    {
        return new self(sprintf('Metadata source "%s" is not readable.', $path));
    }

    public static function missingFrontMatter(string $path): self
    {
        return new self(sprintf('Metadata source "%s" does not contain YAML Front Matter.', $path));
    }

    public static function malformed(string $path, int $line, string $reason): self
    {
        return new self(sprintf('Malformed Front Matter in "%s" at line %d: %s', $path, $line, $reason));
    }
}
