<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;

final class UnsupportedServiceLifetimeException extends
    ContainerResolutionException
{
    public function __construct(
        ServiceIdentifier $requestedIdentifier,
        private readonly ServiceLifetime $lifetime,
        ResolutionPath $path,
    ) {
        parent::__construct(
            requestedIdentifier: $requestedIdentifier,
            path: $path,
            message: sprintf(
                'Service "%s" uses unsupported lifetime "%s".',
                $requestedIdentifier->value(),
                $this->lifetime->value,
            ),
        );
    }

    public function lifetime(): ServiceLifetime
    {
        return $this->lifetime;
    }
}
