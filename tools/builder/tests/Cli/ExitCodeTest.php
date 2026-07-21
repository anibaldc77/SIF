<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\ExitCode;

final class ExitCodeTest extends TestCase
{
    public function testDefinesStableProcessCodes(): void
    {
        self::assertSame(0, ExitCode::SUCCESS->value);
        self::assertSame(2, ExitCode::INVALID_USAGE->value);
        self::assertSame(3, ExitCode::CONFIGURATION_ERROR->value);
        self::assertSame(4, ExitCode::VALIDATION_FAILED->value);
        self::assertSame(5, ExitCode::GENERATION_FAILED->value);
        self::assertSame(6, ExitCode::PARTIAL_SUCCESS->value);
        self::assertSame(10, ExitCode::INTERNAL_ERROR->value);
        self::assertTrue(ExitCode::SUCCESS->isSuccess());
        self::assertFalse(ExitCode::PARTIAL_SUCCESS->isSuccess());
    }
}
