<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

final class ModuleDependencyCycleException extends ModuleRegistryException
{
    /** @param list<string> $moduleIds */
    public static function involving(array $moduleIds): self
    {
        return new self(sprintf('Module dependency cycle detected involving: %s.', implode(', ', $moduleIds)));
    }
}
