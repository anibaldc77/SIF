<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Modules\ModuleVersion;
use Sif\Foundation\Modules\VersionConstraint;

final class IncompatibleModuleVersionException extends ModuleRegistryException
{
    public static function forDependency(ModuleId $module, ModuleId $dependency, ModuleVersion $actual, VersionConstraint $constraint): self
    {
        return new self(sprintf(
            'Module "%s" requires module "%s" matching "%s"; version "%s" is registered.',
            $module->value(),
            $dependency->value(),
            $constraint->value(),
            $actual->value(),
        ));
    }
}
