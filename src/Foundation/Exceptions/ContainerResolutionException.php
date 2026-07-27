<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use RuntimeException;
use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;
use Throwable;

class ContainerResolutionException extends RuntimeException
{
    public function __construct(
        private readonly ServiceIdentifier $requestedIdentifier,
        private readonly ResolutionPath $path,
        string $message,
        ?Throwable $cause = null,
    ) {
        parent::__construct(
            message: $message,
            previous: $cause,
        );
    }

    public function requestedIdentifier(): ServiceIdentifier
    {
        return $this->requestedIdentifier;
    }

    public function path(): ResolutionPath
    {
        return $this->path;
    }

    public function cause(): ?Throwable
    {
        return $this->getPrevious();
    }
}
