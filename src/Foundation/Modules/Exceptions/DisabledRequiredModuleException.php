<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

use Sif\Foundation\Modules\ModuleId;

final class DisabledRequiredModuleException extends ModuleRegistryException
{
    public static function forDependency(ModuleId $dependent, ModuleId $dependency): self
    {
        return new self(sprintf(
            'Enabled module "%s" requires disabled module "%s".',
            $dependent->value(),
            $dependency->value(),
        ));
    }
}
