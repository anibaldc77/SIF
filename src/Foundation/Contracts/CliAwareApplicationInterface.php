<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Cli\Runtime\CliRuntime;

interface CliAwareApplicationInterface extends ApplicationInterface
{
    public function cli(): ?CliRuntime;
}
