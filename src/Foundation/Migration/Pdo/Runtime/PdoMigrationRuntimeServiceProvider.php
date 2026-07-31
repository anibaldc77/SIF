<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableMigrationApplicationInterface;
use Sif\Foundation\ServiceProvider;

final class PdoMigrationRuntimeServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly PdoMigrationRuntimeIntegration $integration)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableMigrationApplicationInterface) {
            $application->setMigrations($this->integration->runtime());
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('migration');
        yield new NamedCapability('migration.pdo');
    }
}
