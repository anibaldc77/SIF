<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Extension;

use Sif\Builder\Engine\Artifact\ArtifactCollection;
use Sif\Builder\Engine\Artifact\GeneratedArtifact;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;

final readonly class GenerationResult
{
    public DiagnosticCollection $diagnostics;
    public ArtifactCollection $artifacts;

    /** @param iterable<GeneratedArtifact> $artifacts */
    public function __construct(?DiagnosticCollection $diagnostics = null, iterable $artifacts = [])
    {
        $this->diagnostics = $diagnostics ?? new DiagnosticCollection();
        $this->artifacts = new ArtifactCollection($artifacts);
    }

    public function isSuccessful(): bool
    {
        return !$this->diagnostics->hasErrors();
    }
}
