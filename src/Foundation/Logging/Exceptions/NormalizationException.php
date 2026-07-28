<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Exceptions;

final class NormalizationException extends LoggingException
{
    public static function because(string $reason): self
    {
        return new self(sprintf('Structured value normalization failed: %s.', $reason));
    }
}
