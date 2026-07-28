<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

use Sif\Foundation\Modules\ModuleId;

final class ModuleConflictException extends ModuleRegistryException
{
    public static function between(ModuleId $module, ModuleId $conflicting): self
    {
        return new self(sprintf('Module "%s" conflicts with registered module "%s".', $module->value(), $conflicting->value()));
    }
}
