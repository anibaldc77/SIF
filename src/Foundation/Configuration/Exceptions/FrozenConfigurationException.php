<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class FrozenConfigurationException extends ConfigurationException
{
    public static function mutationAttempted(): self
    {
        return new self('Frozen configuration cannot be modified.');
    }
}
