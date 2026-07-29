<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Exceptions;

final class ResourceOverrideConflictException extends ResourceException
{
    public static function forbidden(string $resource, string $module): self
    {
        return new self(sprintf('Module "%s" cannot override resource "%s" because its policy forbids replacement.', $module, $resource));
    }

    public static function insufficientPriority(string $resource, string $module, int $candidate, int $current): self
    {
        return new self(sprintf(
            'Module "%s" cannot override resource "%s": priority %d is not greater than %d.',
            $module,
            $resource,
            $candidate,
            $current,
        ));
    }
}
