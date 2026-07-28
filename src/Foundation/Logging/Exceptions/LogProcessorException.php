<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Exceptions;

use Throwable;

final class LogProcessorException extends LoggingException
{
    public static function failed(int $position, string $processor, Throwable $cause): self
    {
        return new self(
            sprintf('Logging processor at position %d (%s) failed.', $position, $processor),
            0,
            $cause,
        );
    }
}
