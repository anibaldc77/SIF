<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\BuilderStatus;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Exception\InvalidBuilderResultException;

final class BuilderResultTest extends TestCase
{
    public function testSuccessfulResultDerivesStatusAndSerializesDeterministically(): void
    {
        $clean = BuilderResult::succeeded([BuilderPhase::PREPARING, BuilderPhase::COMPLETED]);
        $withDiagnostics = BuilderResult::succeeded(
            [BuilderPhase::PREPARING, BuilderPhase::COMPLETED],
            new DiagnosticCollection([
                new Diagnostic('CONFIG-001', DiagnosticSeverity::WARNING, 'Optional configuration missing.'),
            ]),
        );

        self::assertSame(BuilderStatus::SUCCEEDED, $clean->status);
        self::assertSame(BuilderStatus::SUCCEEDED_WITH_DIAGNOSTICS, $withDiagnostics->status);
        self::assertSame('succeeded_with_diagnostics', $withDiagnostics->jsonSerialize()['status']);
        self::assertSame(1, $withDiagnostics->jsonSerialize()['diagnostic_counts']['warning']);
    }

    public function testFailedResultRetainsCauseButDoesNotSerializeIt(): void
    {
        $cause = new RuntimeException('Sensitive internal detail');
        $result = BuilderResult::failed(
            [BuilderPhase::PREPARING],
            'The engine could not prepare the run.',
            cause: $cause,
        );

        self::assertSame($cause, $result->cause());
        self::assertSame(BuilderStatus::FAILED, $result->status);
        self::assertArrayNotHasKey('cause', $result->jsonSerialize());
        self::assertStringNotContainsString('Sensitive', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function testRejectsDuplicateCompletedPhases(): void
    {
        $this->expectException(InvalidBuilderResultException::class);
        BuilderResult::succeeded([BuilderPhase::PREPARING, BuilderPhase::PREPARING]);
    }

    public function testFailedResultRequiresSafeSummary(): void
    {
        $this->expectException(InvalidBuilderResultException::class);
        BuilderResult::failed([], '  ');
    }
}
