<?php

declare(strict_types=1);

namespace Sif\Foundation;

use DateTimeImmutable;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\LifecycleInterface;
use Sif\Foundation\DTO\BootError;

/** Executes provider lifecycle hooks in deterministic order. */
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
                return $this->bootFailure($application, $cause, $startedAt, 'provider.register_failed');
            }
        }

        foreach ($providers as $provider) {
            try {
                $provider->boot($application);
            } catch (\Throwable $cause) {
                return $this->bootFailure($application, $cause, $startedAt, 'provider.boot_failed');
            }
        }

        $application->runtime()->transitionTo(RuntimeState::Booted, BootStage::Booted);

        return BootResult::success(BootStage::Booted, $startedAt, new DateTimeImmutable());
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
            $application->runtime()->fail($firstCause, BootStage::Failed);

            return BootResult::failure(
                BootStage::Failed,
                $startedAt,
                new DateTimeImmutable(),
                $errors,
                $firstCause,
            );
        }

        $application->runtime()->transitionTo(RuntimeState::Stopped, BootStage::Shutdown);

        return BootResult::success(BootStage::Shutdown, $startedAt, new DateTimeImmutable());
    }

    private function bootFailure(
        ApplicationInterface $application,
        \Throwable $cause,
        DateTimeImmutable $startedAt,
        string $code,
    ): BootResult {
        $application->runtime()->fail($cause, BootStage::Failed);

        return BootResult::failure(
            BootStage::Failed,
            $startedAt,
            new DateTimeImmutable(),
            [new BootError($code, $cause->getMessage(), BootStage::Providers)],
            $cause,
        );
    }
}
