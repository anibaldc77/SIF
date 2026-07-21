<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Application;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Contract\ArgumentParserInterface;
use Sif\Builder\Cli\Contract\CliApplicationInterface;
use Sif\Builder\Cli\Exception\ArgumentParsingException;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Registry\CommandRegistry;
use Throwable;

final class CliApplication implements CliApplicationInterface
{
    public function __construct(
        private readonly ArgumentParserInterface $parser,
        private readonly CommandRegistry $commands,
    ) {
    }

    public function run(ArgvInput $input): CommandResult
    {
        $this->commands->freeze();

        try {
            $commandInput = $this->parser->parse($input);
        } catch (ArgumentParsingException $exception) {
            return CommandResult::failure(
                ExitCode::INVALID_USAGE,
                $exception->getMessage(),
            );
        }

        try {
            $command = $this->commands->get($commandInput->commandName);
            if ($command === null) {
                return CommandResult::failure(
                    ExitCode::INVALID_USAGE,
                    sprintf('Unknown command "%s".', $commandInput->commandName),
                );
            }

            return $command->execute($commandInput);
        } catch (Throwable) {
            return CommandResult::failure(
                ExitCode::INTERNAL_ERROR,
                'The command could not be completed because of an internal error.',
            );
        }
    }
}
