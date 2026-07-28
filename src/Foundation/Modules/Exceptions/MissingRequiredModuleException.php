<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

use Sif\Foundation\Modules\ModuleId;

final class MissingRequiredModuleException extends ModuleRegistryException
{
    public static function forDependency(ModuleId $module, ModuleId $dependency): self
    {
        return new self(sprintf('Module "%s" requires missing module "%s".', $module->value(), $dependency->value()));
    }
}
