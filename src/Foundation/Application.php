<?php
declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Contracts\RuntimeInterface;

final class Application implements ApplicationInterface
{
    public function __construct(
        private readonly RuntimeInterface $runtime,
        private readonly KernelInterface $kernel,
        private readonly EnvironmentInterface $environment,
    ) {
    }

    public function runtime(): RuntimeInterface { return $this->runtime; }
    public function kernel(): KernelInterface { return $this->kernel; }
    public function environment(): EnvironmentInterface { return $this->environment; }

    public function boot(): BootResult { return $this->kernel->boot($this); }
    public function run(): BootResult { return $this->kernel->run($this); }
    public function shutdown(): BootResult { return $this->kernel->shutdown($this); }
}
