<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Application;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Application\CliApplication;
use Sif\Builder\Cli\Application\CliRunner;
use Sif\Builder\Cli\Command\VersionCommand;
use Sif\Builder\Cli\Input\ArgumentParser;
use Sif\Builder\Cli\Registry\CommandRegistry;
use Sif\Builder\Cli\Runtime\StaticVersionProvider;
use Sif\Builder\Tests\Cli\Fixtures\MemoryOutput;

final class CliRunnerTest extends TestCase
{
    public function testWritesStandardOutputAndReturnsSuccessCode(): void
    {
        $commands = new CommandRegistry();
        $commands->register(new VersionCommand(new StaticVersionProvider('SIF Builder', '2.0.0-test')));
        $output = new MemoryOutput();
        $runner = new CliRunner(new CliApplication(new ArgumentParser(), $commands), $output);

        self::assertSame(0, $runner->run(['sif-builder', 'version']));
        self::assertSame("SIF Builder 2.0.0-test\n", $output->standardOutput);
        self::assertSame('', $output->standardError);
    }

    public function testWritesUsageErrorsToStandardError(): void
    {
        $output = new MemoryOutput();
        $runner = new CliRunner(new CliApplication(new ArgumentParser(), new CommandRegistry()), $output);

        self::assertSame(2, $runner->run(['sif-builder', 'unknown']));
        self::assertSame('', $output->standardOutput);
        self::assertStringContainsString('Unknown command', $output->standardError);
    }
}
