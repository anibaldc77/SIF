<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableResourceManagementApplicationInterface;
use Sif\Foundation\Resources\Planning\ResourceManagementPlan;
use Sif\Foundation\ServiceProvider;

final class RuntimeResourceManagementServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly ResourceManagementPlan $plan)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableResourceManagementApplicationInterface) {
            $application->setResourceManagement(
                $this->plan,
                $this->plan->createPathResolver(),
            );
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('resource-management');
    }
}
