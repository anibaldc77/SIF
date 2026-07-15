<?php
declare(strict_types=1);

namespace Sif\Foundation;

use DateTimeImmutable;
use Sif\Foundation\Contracts\RuntimeInterface;
use Sif\Foundation\Exceptions\InvalidRuntimeTransitionException;

final class Runtime implements RuntimeInterface
{
    private RuntimeState $state = RuntimeState::Created;
    private BootStage $stage = BootStage::Created;
    private ?\Throwable $failure = null;
    private ?DateTimeImmutable $startedAt = null;
    private ?DateTimeImmutable $stoppedAt = null;

    public function state(): RuntimeState { return $this->state; }
    public function stage(): BootStage { return $this->stage; }
    public function isCreated(): bool { return $this->state === RuntimeState::Created; }
    public function isBootstrapping(): bool { return $this->state === RuntimeState::Bootstrapping; }
    public function isBooted(): bool { return $this->state === RuntimeState::Booted; }
    public function isRunning(): bool { return $this->state === RuntimeState::Running; }
    public function isStopping(): bool { return $this->state === RuntimeState::Stopping; }
    public function isStopped(): bool { return $this->state === RuntimeState::Stopped; }
    public function hasFailed(): bool { return $this->state === RuntimeState::Failed; }

    public function transitionTo(RuntimeState $state, BootStage $stage): void
    {
        $allowed = match ($this->state) {
            RuntimeState::Created => [RuntimeState::Bootstrapping, RuntimeState::Failed],
            RuntimeState::Bootstrapping => [RuntimeState::Booted, RuntimeState::Failed],
            RuntimeState::Booted => [RuntimeState::Running, RuntimeState::Failed],
            RuntimeState::Running => [RuntimeState::Stopping, RuntimeState::Failed],
            RuntimeState::Stopping => [RuntimeState::Stopped, RuntimeState::Failed],
            RuntimeState::Stopped, RuntimeState::Failed => [],
        };
        if (!in_array($state, $allowed, true)) {
            throw InvalidRuntimeTransitionException::between($this->state, $state);
        }
        if ($state === RuntimeState::Bootstrapping) { $this->startedAt = new DateTimeImmutable(); }
        if ($state === RuntimeState::Stopped) { $this->stoppedAt = new DateTimeImmutable(); }
        $this->state = $state;
        $this->stage = $stage;
    }

    public function fail(\Throwable $cause, BootStage $stage): void
    {
        $this->transitionTo(RuntimeState::Failed, $stage);
        $this->failure = $cause;
        $this->stoppedAt = new DateTimeImmutable();
    }
    public function failure(): ?\Throwable { return $this->failure; }
    public function startedAt(): ?DateTimeImmutable { return $this->startedAt; }
    public function stoppedAt(): ?DateTimeImmutable { return $this->stoppedAt; }
}
