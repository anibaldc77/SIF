<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;

final readonly class StageResult
{
    public function __construct(
        public BuilderContext $context,
        ?DiagnosticCollection $diagnostics = null,
    ) {
        $this->diagnostics = $diagnostics ?? new DiagnosticCollection();
    }

    public DiagnosticCollection $diagnostics;

    public function isSuccessful(): bool
    {
        return !$this->diagnostics->hasErrors();
    }
}
