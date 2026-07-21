<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Input;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Exception\InvalidArgvInputException;
use Sif\Builder\Cli\Input\ArgvInput;

final class ArgvInputTest extends TestCase
{
    public function testPhpArgvDropsExecutableToken(): void
    {
        $input = ArgvInput::fromPhpArgv(['bin/sif-builder', 'build', '--strict']);

        self::assertSame(['build', '--strict'], $input->tokens);
    }

    public function testRejectsNullBytes(): void
    {
        $this->expectException(InvalidArgvInputException::class);

        new ArgvInput(["build\0"]);
    }
}
