<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;

interface ServiceResolverInterface extends ServiceContainerInterface
{
    public function resolutionPath(): ResolutionPath;

    public function forget(ServiceIdentifier $identifier): void;

    public function clearSingletons(): void;
}
