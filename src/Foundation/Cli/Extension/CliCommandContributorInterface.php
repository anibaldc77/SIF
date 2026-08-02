<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;

interface CliCommandContributorInterface
{
    /** @return list<CliCommandInterface> */
    public function commands(): array;
}
