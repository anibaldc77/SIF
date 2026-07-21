<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Pipeline\Fixtures;

use RuntimeException;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\AnalysisResult;

final readonly class RecordingAnalyzer implements AnalyzerInterface
{
    /**
     */
    public function __construct(
        private string $identifier,
        private OperationLog $operations,
        private ?DiagnosticSeverity $severity = null,
        private bool $throws = false,
    ) {
    }

    public function id(): string
    {
        return $this->identifier;
    }

    public function analyze(BuilderContext $context): AnalysisResult
    {
        $this->operations->add('analyzer:' . $this->identifier . ':' . $context->phase->value);

        if ($this->throws) {
            throw new RuntimeException('Unsafe internal detail.');
        }

        if ($this->severity === null) {
            return new AnalysisResult();
        }

        return new AnalysisResult(new DiagnosticCollection([
            new Diagnostic(
                code: 'ANALYZER-101',
                severity: $this->severity,
                message: 'Analyzer diagnostic.',
                extension: $this->identifier,
            ),
        ]));
    }
}
