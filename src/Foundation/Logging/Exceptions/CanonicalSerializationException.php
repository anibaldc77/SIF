<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Exceptions;

use Throwable;

final class CanonicalSerializationException extends LoggingException
{
    public static function fromThrowable(Throwable $cause): self
    {
        return new self('Canonical structured value serialization failed.', 0, $cause);
    }
}
