<?php

declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\ServiceProviderInterface;

/** Base extension point with optional boot and shutdown hooks. */
abstract class ServiceProvider implements ServiceProviderInterface
{
    abstract public function register(ApplicationInterface $application): void;

    public function boot(ApplicationInterface $application): void
    {
    }

    public function shutdown(ApplicationInterface $application): void
    {
    }
}
