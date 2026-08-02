<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Cli\Runtime\CliRuntime;

interface MutableCliApplicationInterface extends CliAwareApplicationInterface
{
    public function setCli(CliRuntime $cli): void;
}
