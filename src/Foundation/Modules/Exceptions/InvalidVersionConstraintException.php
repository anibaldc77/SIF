<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

final class InvalidVersionConstraintException extends ModuleRegistryException
{
    public static function forValue(string $value): self
    {
        return new self(sprintf('Invalid module version constraint "%s".', $value));
    }
}
