<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableApplicationSkeletonApplicationInterface;
use Sif\Foundation\ServiceProvider;

final class ApplicationSkeletonRuntimeServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly ApplicationSkeletonRuntime $runtime)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableApplicationSkeletonApplicationInterface) {
            $application->setApplicationSkeleton($this->runtime);
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('application-skeleton');
        yield new NamedCapability('application-skeleton.first-run');
    }
}
