<?php

declare(strict_types=1);

namespace Sif\Foundation;

use DateTimeImmutable;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\CapabilityAwareApplicationInterface;
use Sif\Foundation\Contracts\ConfigurationAwareApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentAwareApplicationInterface;
use Sif\Foundation\Contracts\LifecycleInterface;
use Sif\Foundation\DTO\BootError;

/**
 * Executes provider lifecycle hooks in deterministic order.
 *
 * Runtime state transitions are intentionally excluded. Kernel owns lifecycle
 * authority and applies state changes from the BootResult returned here.
 */
final class Lifecycle implements LifecycleInterface
{
    /** @return list<BootStage> */
    public function bootStages(): array
    {
        return [
            BootStage::Environment,
            BootStage::Bootstrap,
            BootStage::Providers,
            BootStage::Booted,
            BootStage::Running,
        ];
    }

    /** @return list<BootStage> */
    public function shutdownStages(): array
    {
        return [BootStage::Shutdown];
    }

    public function boot(
        ApplicationInterface $application,
        ServiceProviderCollection $providers,
    ): BootResult {
        $startedAt = new DateTimeImmutable();

        foreach ($providers as $provider) {
            try {
                $provider->register($application);
            } catch (\Throwable $cause) {
                return $this->bootFailure(
                    $cause,
                    $startedAt,
                    'provider.register_failed',
                );
            }
        }

        if ($application instanceof CapabilityAwareApplicationInterface) {
            foreach ($providers as $provider) {
                if (!$provider instanceof CapabilityProviderInterface) {
                    continue;
                }

                try {
                    foreach ($provider->capabilities() as $capability) {
                        $application->registerCapability($capability);
                    }
                } catch (\Throwable $cause) {
                    return $this->bootFailure(
                        $cause,
                        $startedAt,
                        'capability.registration_failed',
                    );
                }
            }
        }

        foreach ($providers as $provider) {
            try {
                $provider->boot($application);
            } catch (\Throwable $cause) {
                return $this->bootFailure(
                    $cause,
                    $startedAt,
                    'provider.boot_failed',
                );
            }
        }

        if ($application instanceof ConfigurationAwareApplicationInterface) {
            $application->configuration()->freeze();
        }

        if ($application instanceof EnvironmentAwareApplicationInterface) {
            $application->variables()->freeze();
        }

        return BootResult::success(
            BootStage::Booted,
            $startedAt,
            new DateTimeImmutable(),
        );
    }

    public function shutdown(
        ApplicationInterface $application,
        ServiceProviderCollection $providers,
    ): BootResult {
        $startedAt = new DateTimeImmutable();
        $errors = [];
        $firstCause = null;

        foreach ($providers->reverse() as $provider) {
            try {
                $provider->shutdown($application);
            } catch (\Throwable $cause) {
                $firstCause ??= $cause;
                $errors[] = new BootError(
                    'provider.shutdown_failed',
                    $cause->getMessage(),
                    BootStage::Shutdown,
                    ['provider' => $provider::class],
                );
            }
        }

        if ($firstCause !== null) {
            return BootResult::failure(
                BootStage::Failed,
                $startedAt,
                new DateTimeImmutable(),
                $errors,
                $firstCause,
            );
        }

        return BootResult::success(
            BootStage::Shutdown,
            $startedAt,
            new DateTimeImmutable(),
        );
    }

    private function bootFailure(
        \Throwable $cause,
        DateTimeImmutable $startedAt,
        string $code,
    ): BootResult {
        return BootResult::failure(
            BootStage::Failed,
            $startedAt,
            new DateTimeImmutable(),
            [new BootError(
                $code,
                $cause->getMessage(),
                BootStage::Providers,
            )],
            $cause,
        );
    }
}
