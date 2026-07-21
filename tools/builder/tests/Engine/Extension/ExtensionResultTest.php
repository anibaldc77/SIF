<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Extension;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\AnalysisResult;
use Sif\Builder\Engine\Extension\GenerationResult;

final class ExtensionResultTest extends TestCase
{
    public function testEmptyResultsAreSuccessful(): void
    {
        self::assertTrue((new AnalysisResult())->isSuccessful());
        self::assertTrue((new GenerationResult())->isSuccessful());
    }

    public function testErrorDiagnosticsMakeResultsUnsuccessful(): void
    {
        $diagnostics = new DiagnosticCollection([
            new Diagnostic('ANALYZER-101', DiagnosticSeverity::ERROR, 'Analysis failed.'),
        ]);

        self::assertFalse((new AnalysisResult($diagnostics))->isSuccessful());
        self::assertFalse((new GenerationResult($diagnostics))->isSuccessful());
    }
}
