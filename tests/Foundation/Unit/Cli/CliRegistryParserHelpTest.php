<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Exceptions\CliParseException;
use Sif\Foundation\Cli\Help\CliCommandHelp;
use Sif\Foundation\Cli\Registry\CliCommandRegistry;
use Sif\Foundation\Cli\Parsing\CliInvocationParser;
use Sif\Foundation\Cli\Value\CliArgumentDefinition;
use Sif\Foundation\Cli\Value\CliArgumentName;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Cli\Value\CliOptionDefinition;
use Sif\Foundation\Cli\Value\CliOptionName;

final class CliRegistryParserHelpTest extends TestCase
{
    public function testRegistryResolvesCanonicalNameAndAliasDeterministically(): void
    {
        $registry = new CliCommandRegistry();
        $command = new RegistryParserFixtureCommand();
        $registry->register($command);

        self::assertSame($command, $registry->resolve('runtime:doctor'));
        self::assertSame($command, $registry->resolve('runtime:diagnose'));
        self::assertSame(1, $registry->count());
    }

    public function testParserBuildsValidatedInvocation(): void
    {
        $registry = new CliCommandRegistry();
        $registry->register(new RegistryParserFixtureCommand());
        $parser = new CliInvocationParser($registry);

        $invocation = $parser->parse([
            'runtime:diagnose',
            'full',
            '--format=json',
            '--tag', 'database',
            '--tag', 'runtime',
            '--no-interaction',
            '-v',
        ], ['SIF_ENV' => 'test']);

        self::assertSame('runtime:doctor', $invocation->command()->value());
        self::assertSame('full', $invocation->argument(0));
        self::assertSame(['json'], $invocation->option('format'));
        self::assertSame(['database', 'runtime'], $invocation->option('tag'));
        self::assertFalse($invocation->interaction()->allowsInteraction());
        self::assertSame('verbose', $invocation->verbosity()->value());
    }

    public function testParserRejectsUnknownOption(): void
    {
        $registry = new CliCommandRegistry();
        $registry->register(new RegistryParserFixtureCommand());

        $this->expectException(CliParseException::class);
        (new CliInvocationParser($registry))->parse(['runtime:doctor', 'full', '--unknown']);
    }

    public function testHelpModelIsStructuredAndDeterministic(): void
    {
        $help = new CliCommandHelp((new RegistryParserFixtureCommand())->metadata());
        $data = $help->toArray();

        self::assertSame('sif runtime:doctor <scope> [options]', $data['usage']);
        self::assertSame(['runtime:diagnose'], $data['aliases']);
        self::assertSame('scope', $data['arguments'][0]['name']);
        self::assertSame('format', $data['options'][0]['name']);
    }
}

final class RegistryParserFixtureCommand implements CliCommandInterface
{
    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('runtime:doctor'),
            'Diagnoses the runtime.',
            'Runs deterministic runtime diagnostics.',
            [new CliArgumentDefinition(new CliArgumentName('scope'), 'Diagnostic scope.', true)],
            [
                new CliOptionDefinition(new CliOptionName('format'), 'Output format.', 'f', true),
                new CliOptionDefinition(new CliOptionName('tag'), 'Diagnostic tag.', 't', true, true),
            ],
            CliOperationalClass::inspection(),
            true,
            false,
            [new CliCommandName('runtime:diagnose')],
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        return new CliCommandResult(CliExitCode::success(), 'ok');
    }
}
