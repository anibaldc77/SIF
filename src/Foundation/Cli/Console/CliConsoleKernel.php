<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Console;

use Sif\Foundation\Cli\Contracts\CliInputInterface;
use Sif\Foundation\Cli\Contracts\CliOutputInterface;
use Sif\Foundation\Cli\Exceptions\CliCommandNotFoundException;
use Sif\Foundation\Cli\Exceptions\CliConsoleException;
use Sif\Foundation\Cli\Exceptions\CliParseException;
use Sif\Foundation\Cli\Parsing\CliInvocationParser;
use Sif\Foundation\Cli\Registry\CliCommandRegistry;
use Sif\Foundation\Cli\Rendering\CliCommandResultRenderer;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Throwable;

final readonly class CliConsoleKernel
{
    public function __construct(
        private CliCommandRegistry $registry,
        private CliInvocationParser $parser,
        private CliCommandResultRenderer $renderer,
    ) {
    }

    public function run(CliInputInterface $input, CliOutputInterface $output): int
    {
        try {
            $invocation = $this->parser->parse($input->tokens(), $input->environment());
            $command = $this->registry->resolve($invocation->command());
            $result = $command->execute($invocation);
        } catch (CliCommandNotFoundException $exception) {
            $result = new CliCommandResult(CliExitCode::commandNotFound(), $exception->getMessage());
        } catch (CliParseException $exception) {
            $result = new CliCommandResult(CliExitCode::invalidUsage(), $exception->getMessage());
        } catch (CliConsoleException $exception) {
            $result = new CliCommandResult(CliExitCode::internalFailure(), $exception->getMessage());
        } catch (Throwable $exception) {
            $result = new CliCommandResult(
                CliExitCode::executionFailure(),
                sprintf('Command execution failed: %s', $exception->getMessage()),
            );
        }

        try {
            $this->renderer->render($result, $output);
            return $result->exitCode()->value();
        } catch (Throwable) {
            return CliExitCode::internalFailure()->value();
        }
    }
}
