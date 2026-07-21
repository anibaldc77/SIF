<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Fixtures;

use RuntimeException;
use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Contract\CommandInterface;
use Sif\Builder\Cli\Input\CommandInput;

final class ThrowingCommand implements CommandInterface
{
    public function name(): string
    {
        return 'explode';
    }

    public function description(): string
    {
        return 'Throw an exception.';
    }

    public function execute(CommandInput $input): CommandResult
    {
        throw new RuntimeException('Sensitive internal detail.');
    }
}
