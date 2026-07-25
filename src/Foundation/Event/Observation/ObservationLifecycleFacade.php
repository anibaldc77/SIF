<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\BootResult;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\EventObserverInterface;
use Sif\Foundation\Contracts\KernelInterface;

/**
 * Explicit lifecycle facade for opt-in observed Kernel operations.
 *
 * The decorated Kernel remains authoritative. This facade only simplifies
 * composition and exposes the most recent observation result.
 */
final class ObservationLifecycleFacade implements KernelInterface
{
    private LatestObservationRecorder $recorder;
    private ObservedKernel $kernel;

    public function __construct(
        KernelInterface $kernel,
        EventObserverInterface $observer,
    ) {
        $this->recorder = new LatestObservationRecorder($observer);
        $this->kernel = new ObservedKernel($kernel, $this->recorder);
    }

    public function boot(ApplicationInterface $application): BootResult
    {
        return $this->kernel->boot($application);
    }

    public function run(ApplicationInterface $application): BootResult
    {
        return $this->kernel->run($application);
    }

    public function shutdown(ApplicationInterface $application): BootResult
    {
        return $this->kernel->shutdown($application);
    }

    public function latestObservation(): ?ObservationResult
    {
        return $this->recorder->latest();
    }

    public function hasObservation(): bool
    {
        return $this->recorder->hasResult();
    }

    public function clearObservation(): void
    {
        $this->recorder->clear();
    }
}
