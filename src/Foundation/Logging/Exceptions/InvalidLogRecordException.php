<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Exceptions;

final class InvalidLogRecordException extends LoggingException
{
    public static function because(string $reason): self
    {
        return new self(sprintf('Invalid log record: %s.', $reason));
    }
}
