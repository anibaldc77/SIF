<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

final class InvalidModuleCompositionException extends ModuleRegistryException
{
    public static function missingModule(string $moduleId): self
    {
        return new self(sprintf('Resolved module "%s" is not available in the registry.', $moduleId));
    }

    public static function namespaceMismatch(string $moduleId): self
    {
        return new self(sprintf('Configuration namespace contribution does not match descriptor for module "%s".', $moduleId));
    }

    public static function undeclaredCapability(string $moduleId, string $capability): self
    {
        return new self(sprintf('Module "%s" contributed undeclared capability "%s".', $moduleId, $capability));
    }

    public static function missingCapability(string $moduleId, string $capability): self
    {
        return new self(sprintf('Module "%s" declared capability "%s" but did not contribute it.', $moduleId, $capability));
    }

    public static function invalidServiceProvider(string $moduleId, string $provider): self
    {
        return new self(sprintf('Module "%s" declares invalid service provider "%s".', $moduleId, $provider));
    }
}
