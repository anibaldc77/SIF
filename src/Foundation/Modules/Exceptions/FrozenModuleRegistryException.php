<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

final class FrozenModuleRegistryException extends ModuleRegistryException
{
    public static function mutationAttempted(): self
    {
        return new self('Frozen module registry cannot be modified.');
    }
}
