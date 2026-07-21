<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Application;

use Sif\Builder\Cli\Contract\CliApplicationInterface;
use Sif\Builder\Cli\Contract\OutputInterface;
use Sif\Builder\Cli\Input\ArgvInput;
use Throwable;

final readonly class CliRunner
{
    public function __construct(
        private CliApplicationInterface $application,
        private OutputInterface $output,
    ) {
    }

    /** @param list<string> $argv */
    public function run(array $argv): int
    {
        try {
            $result = $this->application->run(ArgvInput::fromPhpArgv($argv));

            if ($result->standardOutput !== null) {
                $this->output->write($result->standardOutput);
            }
            if ($result->standardError !== null) {
                $this->output->writeError($result->standardError);
            }

            return $result->exitCode->value;
        } catch (Throwable) {
            try {
                $this->output->writeError("SIF Builder terminated because of an internal error.\n");
            } catch (Throwable) {
                // The process still returns a stable code when the output stream itself fails.
            }

            return 10;
        }
    }
}
