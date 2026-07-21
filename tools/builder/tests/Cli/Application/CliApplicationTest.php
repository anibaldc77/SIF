<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Application;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Application\CliApplication;
use Sif\Builder\Cli\Command\VersionCommand;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\ArgumentParser;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Registry\CommandRegistry;
use Sif\Builder\Cli\Runtime\StaticVersionProvider;
use Sif\Builder\Tests\Cli\Fixtures\ThrowingCommand;

final class CliApplicationTest extends TestCase
{
    public function testItDispatchesARegisteredCommandAndFreezesTheRegistry(): void
    {
        $registry = new CommandRegistry();
        $registry->register(new VersionCommand(new StaticVersionProvider('SIF Builder', '2.0.0-alpha1')));
        $application = new CliApplication(new ArgumentParser(), $registry);

        $result = $application->run(new ArgvInput(['version']));

        self::assertSame(ExitCode::SUCCESS, $result->exitCode);
        self::assertSame("SIF Builder 2.0.0-alpha1\n", $result->standardOutput);
        self::assertTrue($registry->isFrozen());
    }

    public function testItMapsParsingAndUnknownCommandErrorsToInvalidUsage(): void
    {
        $registry = new CommandRegistry();
        $application = new CliApplication(new ArgumentParser(), $registry);

        self::assertSame(
            ExitCode::INVALID_USAGE,
            $application->run(new ArgvInput([]))->exitCode,
        );
        self::assertSame(
            ExitCode::INVALID_USAGE,
            $application->run(new ArgvInput(['missing']))->exitCode,
        );
    }

    public function testItDoesNotLeakUnexpectedExceptionDetails(): void
    {
        $registry = new CommandRegistry();
        $registry->register(new ThrowingCommand());
        $application = new CliApplication(new ArgumentParser(), $registry);

        $result = $application->run(new ArgvInput(['explode']));

        self::assertSame(ExitCode::INTERNAL_ERROR, $result->exitCode);
        self::assertStringNotContainsString('Sensitive', (string) $result->standardError);
    }
}
