<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

use Sif\Foundation\Modules\ModuleId;

final class DuplicateModuleException extends ModuleRegistryException
{
    public static function forId(ModuleId $id): self
    {
        return new self(sprintf('Module "%s" is already registered.', $id->value()));
    }
}
