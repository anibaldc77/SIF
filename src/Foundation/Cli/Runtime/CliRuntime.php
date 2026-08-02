<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Runtime;

use Sif\Foundation\Cli\Console\CliConsoleKernel;
use Sif\Foundation\Cli\Contracts\CliInputInterface;
use Sif\Foundation\Cli\Contracts\CliOutputInterface;
use Sif\Foundation\Cli\Help\CliHelpCatalog;
use Sif\Foundation\Cli\Registry\CliCommandRegistry;

final readonly class CliRuntime
{
    public function __construct(
        private CliConsoleKernel $kernel,
        private CliCommandRegistry $commands,
        private CliHelpCatalog $help,
    ) {
    }

    public function run(CliInputInterface $input, CliOutputInterface $output): int
    {
        return $this->kernel->run($input, $output);
    }

    public function commands(): CliCommandRegistry
    {
        return $this->commands;
    }

    public function help(): CliHelpCatalog
    {
        return $this->help;
    }

    /** @return array{command_count: int} */
    public function summary(): array
    {
        return ['command_count' => $this->commands->count()];
    }
}
