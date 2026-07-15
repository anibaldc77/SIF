<?php

declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\BootstrapInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;

final class Bootstrap implements BootstrapInterface
{
    public function createApplication(EnvironmentInterface $environment): ApplicationInterface
    {
        $runtime = new Runtime();
        $lifecycle = new Lifecycle();
        $providers = new ServiceProviderCollection();
        $kernel = new Kernel($lifecycle);

        return new Application($runtime, $kernel, $environment, $providers);
    }
}
