<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Cli;

use LogicException;

final class ResolvedCliConfigurationStore
{
    private ?ResolvedCliConfiguration $configuration = null;

    public function replace(ResolvedCliConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function current(): ResolvedCliConfiguration
    {
        return $this->configuration
            ?? throw new LogicException('CLI repository configuration has not been resolved.');
    }
}
