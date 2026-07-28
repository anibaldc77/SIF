<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

use InvalidArgumentException;

final class InvalidModuleIdException extends InvalidArgumentException
{
    public static function forValue(string $value): self
    {
        return new self(sprintf('Invalid module identifier "%s".', $value));
    }
}
