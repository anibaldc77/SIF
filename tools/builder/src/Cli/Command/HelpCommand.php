<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Command;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Contract\CommandInterface;
use Sif\Builder\Cli\Contract\VersionProviderInterface;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Registry\CommandRegistry;

final readonly class HelpCommand implements CommandInterface
{
    public function __construct(
        private CommandRegistry $commands,
        private VersionProviderInterface $versionProvider,
    ) {
    }

    public function name(): string
    {
        return 'help';
    }

    public function description(): string
    {
        return 'Display global or command-specific help.';
    }

    public function execute(CommandInput $input): CommandResult
    {
        if ($input->options !== [] || $input->flags !== [] || count($input->arguments) > 1) {
            return CommandResult::failure(
                ExitCode::INVALID_USAGE,
                'Usage: sif-builder help [command]',
            );
        }

        $target = $input->argument(0);
        if ($target !== null) {
            $command = $this->commands->get($target);
            if ($command === null) {
                return CommandResult::failure(
                    ExitCode::INVALID_USAGE,
                    sprintf('Unknown command "%s".', $target),
                );
            }

            return CommandResult::success(sprintf(
                "%s\n\nCommand: %s\n%s\n",
                $this->heading(),
                $command->name(),
                $command->description(),
            ));
        }

        $lines = [
            $this->heading(),
            '',
            'Usage:',
            '  sif-builder <command> [arguments] [options]',
            '',
            'Commands:',
        ];

        foreach ($this->commands->all() as $command) {
            $lines[] = sprintf('  %-12s %s', $command->name(), $command->description());
        }

        $lines[] = '';
        $lines[] = 'Run "sif-builder help <command>" for command-specific help.';
        $lines[] = '';

        return CommandResult::success(implode("\n", $lines));
    }

    private function heading(): string
    {
        return sprintf(
            '%s %s',
            $this->versionProvider->applicationName(),
            $this->versionProvider->version(),
        );
    }
}
