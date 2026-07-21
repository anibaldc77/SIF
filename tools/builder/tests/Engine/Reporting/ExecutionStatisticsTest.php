<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Reporting;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Artifact\ArtifactCollection;
use Sif\Builder\Engine\Artifact\GeneratedArtifact;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Reporting\ExecutionStatistics;

final class ExecutionStatisticsTest extends TestCase
{
    public function testItCalculatesDeterministicCounts(): void
    {
        $statistics = ExecutionStatistics::fromExecution(
            [BuilderPhase::PREPARING, BuilderPhase::COMPLETED],
            new DiagnosticCollection([
                new Diagnostic('TEST-101', DiagnosticSeverity::WARNING, 'Warning.'),
                new Diagnostic('TEST-102', DiagnosticSeverity::ERROR, 'Error.'),
            ]),
            new ArtifactCollection([
                new GeneratedArtifact('test.generator', 'report.md', 'markdown', '# Report'),
            ]),
        );

        self::assertSame(2, $statistics->completedPhaseCount);
        self::assertSame(2, $statistics->diagnosticCount);
        self::assertSame(1, $statistics->artifactCount);
        self::assertSame(1, $statistics->diagnosticsBySeverity['warning']);
        self::assertSame(1, $statistics->diagnosticsBySeverity['error']);
    }
}
