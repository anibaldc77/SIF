<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Installer\Runtime\InstallerRuntime;

interface InstallerAwareApplicationInterface extends ApplicationInterface
{
    public function installer(): ?InstallerRuntime;
}
