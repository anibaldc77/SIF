<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use DateTimeImmutable;
use Sif\Foundation\BootResult;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\EventObserverInterface;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Events\RuntimeOperationCompleted;

/**
 * Explicit opt-in Kernel decorator that observes completed operations.
 *
 * The delegate remains authoritative. Observation is attempted only after a
 * BootResult exists, and every observer exception is defensively isolated.
 */
final readonly class ObservedKernel implements KernelInterface
{
    public function __construct(
        private KernelInterface $delegate,
        private EventObserverInterface $observer,
    ) {
    }

    public function boot(ApplicationInterface $application): BootResult
    {
        $result = $this->delegate->boot($application);
        $this->observe($application, RuntimeOperation::Boot, $result);

        return $result;
    }

    public function run(ApplicationInterface $application): BootResult
    {
        $result = $this->delegate->run($application);
        $this->observe($application, RuntimeOperation::Run, $result);

        return $result;
    }

    public function shutdown(ApplicationInterface $application): BootResult
    {
        $result = $this->delegate->shutdown($application);
        $this->observe($application, RuntimeOperation::Shutdown, $result);

        return $result;
    }

    private function observe(
        ApplicationInterface $application,
        RuntimeOperation $operation,
        BootResult $result,
    ): void {
        try {
            $this->observer->observe(
                new RuntimeOperationCompleted(
                    $application,
                    $operation,
                    $result,
                    new DateTimeImmutable(),
                ),
            );
        } catch (\Throwable) {
            // Observation can never acquire lifecycle authority.
        }
    }
}
