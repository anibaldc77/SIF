<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

interface VersionProviderInterface
{
    public function applicationName(): string;

    public function version(): string;
}
