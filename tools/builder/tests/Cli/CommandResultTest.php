<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Exception\InvalidCommandResultException;
use Sif\Builder\Cli\ExitCode;

final class CommandResultTest extends TestCase
{
    public function testCreatesSuccessfulResult(): void
    {
        $result = CommandResult::success("Builder 2.0\n");

        self::assertSame(ExitCode::SUCCESS, $result->exitCode);
        self::assertSame("Builder 2.0\n", $result->standardOutput);
        self::assertNull($result->standardError);
        self::assertNull($result->failureSummary);
    }

    public function testCreatesFailureWithSafeDefaultErrorPayload(): void
    {
        $result = CommandResult::failure(ExitCode::INVALID_USAGE, 'Unknown command.');

        self::assertSame(ExitCode::INVALID_USAGE, $result->exitCode);
        self::assertSame('Unknown command.', $result->standardError);
        self::assertSame('Unknown command.', $result->failureSummary);
    }

    #[DataProvider('invalidResults')]
    public function testRejectsInvalidResult(callable $factory): void
    {
        $this->expectException(InvalidCommandResultException::class);
        $factory();
    }

    /** @return iterable<string, array{callable(): CommandResult}> */
    public static function invalidResults(): iterable
    {
        yield 'success with failure summary' => [
            static fn (): CommandResult => new CommandResult(ExitCode::SUCCESS, failureSummary: 'Failed.'),
        ];
        yield 'failure without summary' => [
            static fn (): CommandResult => new CommandResult(ExitCode::INTERNAL_ERROR),
        ];
        yield 'failure factory with success code' => [
            static fn (): CommandResult => CommandResult::failure(ExitCode::SUCCESS, 'Invalid.'),
        ];
        yield 'null byte output' => [
            static fn (): CommandResult => CommandResult::success("invalid\0output"),
        ];
    }
}
