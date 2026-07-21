<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Reporting;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Artifact\ArtifactCollection;
use Sif\Builder\Engine\Artifact\GeneratedArtifact;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Reporting\MarkdownBuilderReporter;

final class MarkdownBuilderReporterTest extends TestCase
{
    public function testItRendersSummaryDiagnosticsAndArtifacts(): void
    {
        $result = BuilderResult::succeeded(
            [BuilderPhase::ANALYZING, BuilderPhase::COMPLETED],
            new DiagnosticCollection([
                new Diagnostic('REFERENCE-404', DiagnosticSeverity::WARNING, 'Missing | target.', 'ADR-001'),
            ]),
            new ArtifactCollection([
                new GeneratedArtifact('repository.index', 'engineering/INDEX.md', 'markdown', '# Index'),
            ]),
            runIdentifier: 'run-002',
        );

        $content = (new MarkdownBuilderReporter())->render($result);

        self::assertStringContainsString('# SIF Builder Execution Report', $content);
        self::assertStringContainsString('`run-002`', $content);
        self::assertStringContainsString('REFERENCE-404', $content);
        self::assertStringContainsString('Missing \\| target.', $content);
        self::assertStringContainsString('engineering/INDEX.md', $content);
    }
}
