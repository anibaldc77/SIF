<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

use Sif\Builder\Engine\Artifact\ArtifactCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;

final readonly class StageResult
{
    public DiagnosticCollection $diagnostics;
    public ArtifactCollection $artifacts;

    public function __construct(
        public BuilderContext $context,
        ?DiagnosticCollection $diagnostics = null,
        ?ArtifactCollection $artifacts = null,
    ) {
        $this->diagnostics = $diagnostics ?? new DiagnosticCollection();
        $this->artifacts = $artifacts ?? new ArtifactCollection();
    }

    public function isSuccessful(): bool
    {
        return !$this->diagnostics->hasErrors();
    }
}
