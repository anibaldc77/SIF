<?php
declare(strict_types=1);
namespace Sif\Foundation\Contracts;
use DateTimeImmutable; use Sif\Foundation\BootStage; use Sif\Foundation\RuntimeState; use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;
interface RuntimeInterface { public function environment(): EnvironmentProviderInterface; public function state(): RuntimeState; public function stage(): BootStage; public function isCreated(): bool; public function isBootstrapping(): bool; public function isBooted(): bool; public function isRunning(): bool; public function isStopping(): bool; public function isStopped(): bool; public function hasFailed(): bool; public function transitionTo(RuntimeState $state, BootStage $stage): void; public function fail(\Throwable $cause, BootStage $stage): void; public function failure(): ?\Throwable; public function startedAt(): ?DateTimeImmutable; public function stoppedAt(): ?DateTimeImmutable; }
