<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Exception;

use RuntimeException;

final class ReferenceParseException extends RuntimeException
{
    public static function invalidIdentifier(string $path, string $field, string $identifier): self
    {
        return new self(sprintf(
            'Invalid reference identifier "%s" in field "%s" of "%s".',
            $identifier,
            $field,
            $path,
        ));
    }

    public static function invalidFieldValue(string $path, string $field): self
    {
        return new self(sprintf(
            'Reference field "%s" in "%s" must be null, a string, or a list of strings.',
            $field,
            $path,
        ));
    }

    public static function duplicate(string $path, string $field, string $identifier): self
    {
        return new self(sprintf(
            'Duplicate reference "%s" in field "%s" of "%s".',
            $identifier,
            $field,
            $path,
        ));
    }
}
