<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Extension;

use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;

final readonly class AnalysisResult
{
    public DiagnosticCollection $diagnostics;

    public function __construct(?DiagnosticCollection $diagnostics = null)
    {
        $this->diagnostics = $diagnostics ?? new DiagnosticCollection();
    }

    public function isSuccessful(): bool
    {
        return !$this->diagnostics->hasErrors();
    }
}
