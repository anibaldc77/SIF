<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Reporting;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Reporting\BuilderExitCodeMapper;
use Sif\Builder\Cli\Reporting\ExecutionCommandType;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

final class BuilderExitCodeMapperTest extends TestCase
{
    public function testMapsSuccessfulExecution(): void
    {
        $result = BuilderResult::succeeded([]);

        self::assertSame(ExitCode::SUCCESS, (new BuilderExitCodeMapper())->map(ExecutionCommandType::BUILD, $result));
    }

    public function testValidationErrorsMapToValidationFailure(): void
    {
        $result = BuilderResult::succeeded([], new DiagnosticCollection([
            new Diagnostic('REFERENCE-404', DiagnosticSeverity::ERROR, 'Missing reference.'),
        ]));

        self::assertSame(ExitCode::VALIDATION_FAILED, (new BuilderExitCodeMapper())->map(ExecutionCommandType::VALIDATE, $result));
    }

    public function testConfigurationDiagnosticsTakePrecedence(): void
    {
        $result = BuilderResult::succeeded([], new DiagnosticCollection([
            new Diagnostic('CONFIG-101', DiagnosticSeverity::ERROR, 'Unknown analyzer.'),
        ]));

        self::assertSame(ExitCode::CONFIGURATION_ERROR, (new BuilderExitCodeMapper())->map(ExecutionCommandType::BUILD, $result));
    }
}
