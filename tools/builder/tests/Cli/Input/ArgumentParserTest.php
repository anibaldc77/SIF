<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Input;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Exception\ArgumentParsingException;
use Sif\Builder\Cli\Input\ArgumentParser;
use Sif\Builder\Cli\Input\ArgvInput;

final class ArgumentParserTest extends TestCase
{
    public function testParsesArgumentsOptionsFlagsAndRepeatedOptions(): void
    {
        $input = (new ArgumentParser())->parse(new ArgvInput([
            'build',
            'D:/SIF',
            '--output=build/generated',
            '--analyzer', 'reference.broken',
            '--analyzer=repository.metadata',
            '--strict',
        ]));

        self::assertSame('build', $input->commandName);
        self::assertSame(['D:/SIF'], $input->arguments);
        self::assertSame('build/generated', $input->option('output'));
        self::assertSame(['reference.broken', 'repository.metadata'], $input->optionValues('analyzer'));
        self::assertTrue($input->hasFlag('strict'));
    }

    public function testEndOfOptionsPreservesFollowingTokensAsArguments(): void
    {
        $input = (new ArgumentParser())->parse(new ArgvInput(['build', '--', '--strict', '-x']));

        self::assertSame(['--strict', '-x'], $input->arguments);
        self::assertFalse($input->hasFlag('strict'));
    }

    #[DataProvider('invalidArguments')]
    public function testRejectsInvalidArguments(array $tokens): void
    {
        $this->expectException(ArgumentParsingException::class);

        (new ArgumentParser())->parse(new ArgvInput($tokens));
    }

    /** @return iterable<string, array{0: list<string>}> */
    public static function invalidArguments(): iterable
    {
        yield 'missing command' => [[]];
        yield 'option before command' => [['--help']];
        yield 'unknown option' => [['build', '--unknown']];
        yield 'missing value' => [['build', '--output']];
        yield 'empty inline value' => [['build', '--format=']];
        yield 'value assigned to flag' => [['build', '--strict=yes']];
        yield 'duplicate flag' => [['build', '--strict', '--strict']];
        yield 'short option' => [['build', '-v']];
        yield 'quiet and verbose' => [['build', '--quiet', '--verbose']];
        yield 'strict and lenient' => [['build', '--strict', '--lenient']];
    }
}
