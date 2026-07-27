<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;

final class CircularServiceDependencyException extends
    ContainerResolutionException
{
    public function __construct(
        ServiceIdentifier $requestedIdentifier,
        ResolutionPath $path,
    ) {
        parent::__construct(
            requestedIdentifier: $requestedIdentifier,
            path: $path,
            message: sprintf(
                'Circular service dependency detected: %s.',
                $path->format(),
            ),
        );
    }
}
