<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

use JsonSerializable;
use Sif\Builder\Engine\Artifact\ArtifactCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Exception\InvalidBuilderResultException;
use Sif\Builder\Engine\Reporting\ExecutionStatistics;
use Throwable;

final readonly class BuilderResult implements JsonSerializable
{
    /** @var list<BuilderPhase> */
    public array $completedPhases;
    public DiagnosticCollection $diagnostics;
    public ArtifactCollection $artifacts;
    public ExecutionStatistics $statistics;

    /** @param list<BuilderPhase> $completedPhases */
    public function __construct(
        public BuilderStatus $status,
        array $completedPhases,
        ?DiagnosticCollection $diagnostics = null,
        public ?string $failureSummary = null,
        private ?Throwable $cause = null,
        ?ArtifactCollection $artifacts = null,
        ?ExecutionStatistics $statistics = null,
        public ?string $runIdentifier = null,
    ) {
        foreach ($completedPhases as $phase) {
            if (!$phase instanceof BuilderPhase) {
                throw new InvalidBuilderResultException('Completed phases must contain only BuilderPhase values.');
            }
        }
        $seen = [];
        foreach ($completedPhases as $phase) {
            if (isset($seen[$phase->value])) {
                throw new InvalidBuilderResultException('Completed phases must not contain duplicates.');
            }
            $seen[$phase->value] = true;
        }
        if ($this->status === BuilderStatus::FAILED && trim((string) $this->failureSummary) === '') {
            throw new InvalidBuilderResultException('Failed results require a safe failure summary.');
        }
        if ($this->status !== BuilderStatus::FAILED && $this->failureSummary !== null) {
            throw new InvalidBuilderResultException('Successful results must not contain a failure summary.');
        }
        if ($this->runIdentifier !== null && trim($this->runIdentifier) === '') {
            throw new InvalidBuilderResultException('Run identifier must be null or non-empty.');
        }

        $this->completedPhases = array_values($completedPhases);
        $this->diagnostics = $diagnostics ?? new DiagnosticCollection();
        $this->artifacts = $artifacts ?? new ArtifactCollection();
        $this->statistics = $statistics ?? ExecutionStatistics::fromExecution(
            $this->completedPhases,
            $this->diagnostics,
            $this->artifacts,
        );
    }

    public static function succeeded(
        array $completedPhases,
        ?DiagnosticCollection $diagnostics = null,
        ?ArtifactCollection $artifacts = null,
        ?ExecutionStatistics $statistics = null,
        ?string $runIdentifier = null,
    ): self {
        $diagnostics ??= new DiagnosticCollection();
        $status = $diagnostics->isEmpty() ? BuilderStatus::SUCCEEDED : BuilderStatus::SUCCEEDED_WITH_DIAGNOSTICS;

        return new self($status, $completedPhases, $diagnostics, null, null, $artifacts, $statistics, $runIdentifier);
    }

    public static function failed(
        array $completedPhases,
        string $failureSummary,
        ?DiagnosticCollection $diagnostics = null,
        ?Throwable $cause = null,
        ?ArtifactCollection $artifacts = null,
        ?ExecutionStatistics $statistics = null,
        ?string $runIdentifier = null,
    ): self {
        return new self(
            BuilderStatus::FAILED,
            $completedPhases,
            $diagnostics,
            $failureSummary,
            $cause,
            $artifacts,
            $statistics,
            $runIdentifier,
        );
    }

    public function cause(): ?Throwable
    {
        return $this->cause;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status->value,
            'run_identifier' => $this->runIdentifier,
            'completed_phases' => array_map(
                static fn (BuilderPhase $phase): string => $phase->value,
                $this->completedPhases,
            ),
            'diagnostics' => $this->diagnostics->all(),
            'diagnostic_counts' => $this->statistics->diagnosticsBySeverity,
            'artifacts' => $this->artifacts->all(),
            'statistics' => $this->statistics,
            'failure_summary' => $this->failureSummary,
        ];
    }
}
