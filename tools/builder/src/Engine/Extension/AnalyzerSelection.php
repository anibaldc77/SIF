<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Extension;

use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;

final readonly class AnalyzerSelection
{
    /** @var list<AnalyzerInterface> */
    public array $analyzers;

    public DiagnosticCollection $diagnostics;

    /**
     * @param list<AnalyzerInterface> $analyzers
     */
    public function __construct(array $analyzers, ?DiagnosticCollection $diagnostics = null)
    {
        $this->analyzers = array_values($analyzers);
        $this->diagnostics = $diagnostics ?? new DiagnosticCollection();
    }
}
