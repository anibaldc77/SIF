<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Reporting;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Reporting\BuilderCommandResultFactory;
use Sif\Builder\Cli\Reporting\ExecutionCommandType;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

final class BuilderCommandResultFactoryTest extends TestCase
{
    public function testRendersJsonOnStandardOutput(): void
    {
        $commandResult = (new BuilderCommandResultFactory())->create(
            ExecutionCommandType::BUILD,
            new CommandInput('build', [], ['format' => ['json']]),
            BuilderResult::succeeded([]),
        );

        self::assertSame(ExitCode::SUCCESS, $commandResult->exitCode);
        self::assertStringContainsString('"status": "succeeded"', (string) $commandResult->standardOutput);
        self::assertNull($commandResult->standardError);
    }

    public function testQuietSuppressesSuccessfulHumanReport(): void
    {
        $commandResult = (new BuilderCommandResultFactory())->create(
            ExecutionCommandType::BUILD,
            new CommandInput('build', [], [], ['quiet']),
            BuilderResult::succeeded([]),
        );

        self::assertSame(ExitCode::SUCCESS, $commandResult->exitCode);
        self::assertNull($commandResult->standardOutput);
    }

    public function testValidationFailureKeepsReportAndErrorSeparated(): void
    {
        $builderResult = BuilderResult::succeeded([], new DiagnosticCollection([
            new Diagnostic('REFERENCE-404', DiagnosticSeverity::ERROR, 'Missing reference.'),
        ]));

        $commandResult = (new BuilderCommandResultFactory())->create(
            ExecutionCommandType::VALIDATE,
            new CommandInput('validate'),
            $builderResult,
        );

        self::assertSame(ExitCode::VALIDATION_FAILED, $commandResult->exitCode);
        self::assertStringContainsString('# SIF Builder Execution Report', (string) $commandResult->standardOutput);
        self::assertSame('Repository validation failed.', $commandResult->standardError);
    }
}
