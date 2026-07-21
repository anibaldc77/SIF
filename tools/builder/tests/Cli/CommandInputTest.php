<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Exception\InvalidCommandInputException;
use Sif\Builder\Cli\Input\CommandInput;

final class CommandInputTest extends TestCase
{
    public function testNormalizesNamesAndPreservesRepeatedOptionValues(): void
    {
        $input = new CommandInput(
            commandName: ' BUILD ',
            arguments: ['D:\\SIF', 'literal value'],
            options: [
                '--FORMAT' => ['json'],
                'analyzer' => ['reference.broken', 'repository.metadata'],
            ],
            flags: ['--STRICT', 'verbose'],
        );

        self::assertSame('build', $input->commandName);
        self::assertSame(['D:\\SIF', 'literal value'], $input->arguments);
        self::assertSame('D:\\SIF', $input->argument(0));
        self::assertNull($input->argument(9));
        self::assertSame('json', $input->option('format'));
        self::assertSame(['reference.broken', 'repository.metadata'], $input->optionValues('ANALYZER'));
        self::assertTrue($input->hasOption('--format'));
        self::assertTrue($input->hasFlag('strict'));
        self::assertFalse($input->hasFlag('quiet'));
    }

    #[DataProvider('invalidInputs')]
    public function testRejectsInvalidInput(callable $factory): void
    {
        $this->expectException(InvalidCommandInputException::class);
        $factory();
    }

    /** @return iterable<string, array{callable(): CommandInput}> */
    public static function invalidInputs(): iterable
    {
        yield 'invalid command' => [static fn (): CommandInput => new CommandInput('build command')];
        yield 'non string argument' => [static fn (): CommandInput => new CommandInput('build', [1])];
        yield 'empty option values' => [static fn (): CommandInput => new CommandInput('build', options: ['format' => []])];
        yield 'duplicate normalized option' => [
            static fn (): CommandInput => new CommandInput('build', options: ['format' => ['json'], '--FORMAT' => ['markdown']]),
        ];
        yield 'duplicate flag' => [static fn (): CommandInput => new CommandInput('build', flags: ['strict', '--STRICT'])];
        yield 'option flag collision' => [
            static fn (): CommandInput => new CommandInput('build', options: ['strict' => ['yes']], flags: ['strict']),
        ];
    }
}
