<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableInstallerApplicationInterface;
use Sif\Foundation\ServiceProvider;

final class RuntimeInstallerServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly InstallerRuntime $installer)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableInstallerApplicationInterface) {
            $application->setInstaller($this->installer);
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('installer');
    }
}
