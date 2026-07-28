<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

use RuntimeException;
use Throwable;

final class ModuleRuntimeIntegrationException extends RuntimeException
{
    public static function missingCapability(string $moduleId, string $capability): self
    {
        return new self(sprintf(
            'Module "%s" requires unavailable capability "%s".',
            $moduleId,
            $capability,
        ));
    }

    public static function providerInstantiation(string $provider, Throwable $cause): self
    {
        return new self(
            sprintf('Service provider "%s" could not be instantiated.', $provider),
            0,
            $cause,
        );
    }
}
