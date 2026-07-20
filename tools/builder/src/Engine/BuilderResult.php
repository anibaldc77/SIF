<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

use JsonSerializable;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Exception\InvalidBuilderResultException;
use Throwable;

final readonly class BuilderResult implements JsonSerializable
{
    /** @var list<BuilderPhase> */
    public array $completedPhases;

    public DiagnosticCollection $diagnostics;

    /**
     * @param list<BuilderPhase> $completedPhases
     */
    public function __construct(
        public BuilderStatus $status,
        array $completedPhases,
        ?DiagnosticCollection $diagnostics = null,
        public ?string $failureSummary = null,
        private ?Throwable $cause = null,
    ) {
        foreach ($completedPhases as $phase) {
            if (!$phase instanceof BuilderPhase) {
                throw new InvalidBuilderResultException('Completed phases must contain only BuilderPhase values.');
            }
        }

        $seenPhases = [];
        foreach ($completedPhases as $phase) {
            if (isset($seenPhases[$phase->value])) {
                throw new InvalidBuilderResultException('Completed phases must not contain duplicates.');
            }

            $seenPhases[$phase->value] = true;
        }

        if ($this->status === BuilderStatus::FAILED && trim((string) $this->failureSummary) === '') {
            throw new InvalidBuilderResultException('Failed results require a safe failure summary.');
        }

        if ($this->status !== BuilderStatus::FAILED && $this->failureSummary !== null) {
            throw new InvalidBuilderResultException('Successful results must not contain a failure summary.');
        }

        $this->completedPhases = array_values($completedPhases);
        $this->diagnostics = $diagnostics ?? new DiagnosticCollection();
    }

    public static function succeeded(
        array $completedPhases,
        ?DiagnosticCollection $diagnostics = null,
    ): self {
        $diagnostics ??= new DiagnosticCollection();
        $status = $diagnostics->isEmpty()
            ? BuilderStatus::SUCCEEDED
            : BuilderStatus::SUCCEEDED_WITH_DIAGNOSTICS;

        return new self($status, $completedPhases, $diagnostics);
    }

    public static function failed(
        array $completedPhases,
        string $failureSummary,
        ?DiagnosticCollection $diagnostics = null,
        ?Throwable $cause = null,
    ): self {
        return new self(
            BuilderStatus::FAILED,
            $completedPhases,
            $diagnostics,
            $failureSummary,
            $cause,
        );
    }

    public function cause(): ?Throwable
    {
        return $this->cause;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        $counts = [];
        foreach (DiagnosticSeverity::cases() as $severity) {
            $counts[$severity->label()] = 0;
        }

        foreach ($this->diagnostics as $diagnostic) {
            ++$counts[$diagnostic->severity->label()];
        }

        return [
            'status' => $this->status->value,
            'completed_phases' => array_map(
                static fn (BuilderPhase $phase): string => $phase->value,
                $this->completedPhases,
            ),
            'diagnostics' => $this->diagnostics->all(),
            'diagnostic_counts' => $counts,
            'failure_summary' => $this->failureSummary,
        ];
    }
}
