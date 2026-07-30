<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Installer\Runtime\InstallerRuntime;

interface MutableInstallerApplicationInterface extends InstallerAwareApplicationInterface
{
    public function setInstaller(InstallerRuntime $installer): void;
}
