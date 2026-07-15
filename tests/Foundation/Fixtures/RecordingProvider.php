<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures;

use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\ServiceProvider;

abstract class RecordingProvider extends ServiceProvider
{
    public function __construct(
        private readonly OperationLog $log,
        private readonly string $label,
        private readonly ?string $failureOperation = null,
        private readonly ?\Throwable $failure = null,
    ) {
    }

    public function register(ApplicationInterface $application): void
    {
        $this->record('register');
    }

    public function boot(ApplicationInterface $application): void
    {
        $this->record('boot');
    }

    public function shutdown(ApplicationInterface $application): void
    {
        $this->record('shutdown');
    }

    private function record(string $operation): void
    {
        $this->log->entries[] = $this->label . '.' . $operation;

        if ($this->failureOperation === $operation) {
            throw $this->failure ?? new \RuntimeException($this->label . ' ' . $operation . ' failed');
        }
    }
}
