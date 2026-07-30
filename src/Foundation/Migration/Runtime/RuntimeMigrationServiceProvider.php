<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableMigrationApplicationInterface;
use Sif\Foundation\ServiceProvider;

final class RuntimeMigrationServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly MigrationRuntime $migrations)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableMigrationApplicationInterface) {
            $application->setMigrations($this->migrations);
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('migration');
    }
}
