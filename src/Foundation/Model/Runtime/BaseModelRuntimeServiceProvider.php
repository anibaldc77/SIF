<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableBaseModelApplicationInterface;
use Sif\Foundation\ServiceProvider;

final class BaseModelRuntimeServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly BaseModelRuntime $runtime)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableBaseModelApplicationInterface) {
            $application->setModels($this->runtime);
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('models');
        yield new NamedCapability('models.basemodel2');
    }
}
