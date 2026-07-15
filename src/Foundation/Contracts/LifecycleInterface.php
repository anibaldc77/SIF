<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\BootResult;
use Sif\Foundation\BootStage;
use Sif\Foundation\ServiceProviderCollection;

interface LifecycleInterface
{
    /** @return list<BootStage> */
    public function bootStages(): array;

    /** @return list<BootStage> */
    public function shutdownStages(): array;

    public function boot(
        ApplicationInterface $application,
        ServiceProviderCollection $providers,
    ): BootResult;

    public function shutdown(
        ApplicationInterface $application,
        ServiceProviderCollection $providers,
    ): BootResult;
}
