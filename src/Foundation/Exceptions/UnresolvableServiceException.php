<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;

class UnresolvableServiceException extends ContainerResolutionException
{
    public function __construct(
        ServiceIdentifier $requestedIdentifier,
        ResolutionPath $path,
        string $message,
    ) {
        parent::__construct(
            requestedIdentifier: $requestedIdentifier,
            path: $path,
            message: $message,
        );
    }
}
