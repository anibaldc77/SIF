<?php

declare(strict_types=1);

namespace Sif\Foundation;

use DateTimeImmutable;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Contracts\LifecycleInterface;
use Sif\Foundation\DTO\BootError;

final readonly class Kernel implements KernelInterface
{
    public function __construct(private LifecycleInterface $lifecycle)
    {
    }

    public function boot(ApplicationInterface $application): BootResult
    {
        $startedAt = new DateTimeImmutable();

        try {
            $application->runtime()->transitionTo(
                RuntimeState::Bootstrapping,
                BootStage::Bootstrap,
            );

            $result = $this->lifecycle->boot($application, $application->providers());

            if ($result->failed()) {
                $this->markFailed($application, $result->cause());

                return $result;
            }

            $application->runtime()->transitionTo(
                RuntimeState::Booted,
                BootStage::Booted,
            );

            return $result;
        } catch (\Throwable $cause) {
            return $this->failure($application, $cause, $startedAt);
        }
    }

    public function run(ApplicationInterface $application): BootResult
    {
        $startedAt = new DateTimeImmutable();

        if ($application->runtime()->isCreated()) {
            $bootResult = $this->boot($application);

            if ($bootResult->failed()) {
                return $bootResult;
            }
        }

        try {
            $application->runtime()->transitionTo(
                RuntimeState::Running,
                BootStage::Running,
            );

            return BootResult::success(
                BootStage::Running,
                $startedAt,
                new DateTimeImmutable(),
            );
        } catch (\Throwable $cause) {
            return $this->failure($application, $cause, $startedAt);
        }
    }

    public function shutdown(ApplicationInterface $application): BootResult
    {
        $startedAt = new DateTimeImmutable();

        try {
            $application->runtime()->transitionTo(
                RuntimeState::Stopping,
                BootStage::Shutdown,
            );

            $result = $this->lifecycle->shutdown(
                $application,
                $application->providers(),
            );

            if ($result->failed()) {
                $this->markFailed($application, $result->cause());

                return $result;
            }

            $application->runtime()->transitionTo(
                RuntimeState::Stopped,
                BootStage::Shutdown,
            );

            return $result;
        } catch (\Throwable $cause) {
            return $this->failure($application, $cause, $startedAt);
        }
    }

    private function failure(
        ApplicationInterface $application,
        \Throwable $cause,
        DateTimeImmutable $startedAt,
    ): BootResult {
        $this->markFailed($application, $cause);

        return BootResult::failure(
            BootStage::Failed,
            $startedAt,
            new DateTimeImmutable(),
            [new BootError(
                'kernel.failure',
                $cause->getMessage(),
                BootStage::Failed,
            )],
            $cause,
        );
    }

    private function markFailed(
        ApplicationInterface $application,
        ?\Throwable $cause,
    ): void {
        if (
            $cause === null
            || $application->runtime()->hasFailed()
            || $application->runtime()->isStopped()
        ) {
            return;
        }

        $application->runtime()->fail($cause, BootStage::Failed);
    }
}
