<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

final class InvalidModuleContributionException extends \InvalidArgumentException
{
    public static function invalidConfigurationNamespace(string $namespace): self
    {
        return new self(sprintf('Invalid module configuration namespace "%s".', $namespace));
    }

    public static function missingConfigurationNamespace(): self
    {
        return new self('Configuration sources require an explicit module configuration namespace.');
    }

    public static function duplicateConfigurationSource(string $id): self
    {
        return new self(sprintf('Duplicate module configuration source "%s".', $id));
    }

    public static function duplicateServiceDefinition(string $id): self
    {
        return new self(sprintf('Duplicate module service definition "%s".', $id));
    }

    public static function duplicateCapability(string $id): self
    {
        return new self(sprintf('Duplicate module capability "%s".', $id));
    }
}
