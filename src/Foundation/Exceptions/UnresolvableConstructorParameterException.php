<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;

final class UnresolvableConstructorParameterException extends
    UnresolvableServiceException
{
    public function __construct(
        ServiceIdentifier $requestedIdentifier,
        private readonly string $parameterName,
        ResolutionPath $path,
        string $message,
    ) {
        parent::__construct(
            requestedIdentifier: $requestedIdentifier,
            path: $path,
            message: $message,
        );
    }

    public function parameterName(): string
    {
        return $this->parameterName;
    }
}
