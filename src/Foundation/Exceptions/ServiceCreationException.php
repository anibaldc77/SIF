<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;
use Throwable;

final class ServiceCreationException extends ContainerResolutionException
{
    public function __construct(
        ServiceIdentifier $requestedIdentifier,
        ResolutionPath $path,
        Throwable $cause,
        ?string $message = null,
    ) {
        parent::__construct(
            requestedIdentifier: $requestedIdentifier,
            path: $path,
            message: $message ?? sprintf(
                'Unable to create service "%s".',
                $requestedIdentifier->value(),
            ),
            cause: $cause,
        );
    }
}
