<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

final class InvalidBootResultException extends FoundationException
{
    public static function missingErrors(): self
    {
        return new self('A failed boot result requires at least one error.');
    }
}
