<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;

final class MissingActiveServiceScopeException extends
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
                'Scoped service "%s" requires an active service scope.',
                $requestedIdentifier->value(),
            ),
        );
    }
}
