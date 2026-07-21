<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Reporting;

use JsonSerializable;
use Sif\Builder\Engine\Artifact\ArtifactCollection;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

final readonly class ExecutionStatistics implements JsonSerializable
{
    /** @var array<string, int> */
    public array $diagnosticsBySeverity;

    /**
     * @param array<string, int> $diagnosticsBySeverity
     */
    public function __construct(
        public int $completedPhaseCount,
        public int $diagnosticCount,
        array $diagnosticsBySeverity,
        public int $artifactCount,
    ) {
        ksort($diagnosticsBySeverity);
        $this->diagnosticsBySeverity = $diagnosticsBySeverity;
    }

    /** @param list<BuilderPhase> $completedPhases */
    public static function fromExecution(
        array $completedPhases,
        DiagnosticCollection $diagnostics,
        ArtifactCollection $artifacts,
    ): self {
        $counts = [];
        foreach (DiagnosticSeverity::cases() as $severity) {
            $counts[$severity->label()] = 0;
        }
        foreach ($diagnostics as $diagnostic) {
            ++$counts[$diagnostic->severity->label()];
        }

        return new self(count($completedPhases), count($diagnostics), $counts, count($artifacts));
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'completed_phase_count' => $this->completedPhaseCount,
            'diagnostic_count' => $this->diagnosticCount,
            'diagnostics_by_severity' => $this->diagnosticsBySeverity,
            'artifact_count' => $this->artifactCount,
        ];
    }
}
