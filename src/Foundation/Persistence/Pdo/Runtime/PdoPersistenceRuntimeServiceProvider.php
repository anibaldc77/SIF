<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutablePersistenceApplicationInterface;
use Sif\Foundation\ServiceProvider;

final class PdoPersistenceRuntimeServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly PdoPersistenceRuntime $runtime)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutablePersistenceApplicationInterface) {
            $application->setPersistence($this->runtime);
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('persistence');
        yield new NamedCapability('persistence.pdo');
    }
}
