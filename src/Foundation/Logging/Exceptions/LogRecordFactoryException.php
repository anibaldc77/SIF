<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Exceptions;

final class LogRecordFactoryException extends LoggingException
{
    public static function because(string $reason): self
    {
        return new self(sprintf('Log record creation failed: %s.', $reason));
    }
}
