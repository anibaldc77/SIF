<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Fixtures;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Contract\CommandInterface;
use Sif\Builder\Cli\Input\CommandInput;

final readonly class StubCommand implements CommandInterface
{
    public function __construct(
        private string $commandName,
        private string $commandDescription = 'Fixture command',
    ) {
    }

    public function name(): string
    {
        return $this->commandName;
    }

    public function description(): string
    {
        return $this->commandDescription;
    }

    public function execute(CommandInput $input): CommandResult
    {
        return CommandResult::success($input->commandName);
    }
}
