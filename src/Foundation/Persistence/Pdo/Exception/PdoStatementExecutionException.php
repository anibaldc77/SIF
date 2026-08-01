<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Exception;

use Throwable;

final class PdoStatementExecutionException extends PdoPersistenceException
{
    public static function preparationFailed(?Throwable $previous = null): self
    {
        return new self('PDO statement preparation failed.', 0, $previous);
    }

    public static function executionFailed(?Throwable $previous = null): self
    {
        return new self('PDO statement execution failed.', 0, $previous);
    }

    public static function resultAdaptationFailed(?Throwable $previous = null): self
    {
        return new self('PDO result adaptation failed.', 0, $previous);
    }
}
