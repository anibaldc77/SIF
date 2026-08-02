<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableCliApplicationInterface;
use Sif\Foundation\ServiceProvider;

final class CliRuntimeServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly CliRuntime $runtime)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableCliApplicationInterface) {
            $application->setCli($this->runtime);
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('cli');
        yield new NamedCapability('cli.developer');
    }
}
