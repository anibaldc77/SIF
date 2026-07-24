<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\ObservationFailureReporterInterface;

/** Records observation diagnostics in insertion order for host inspection. */
final class InMemoryObservationFailureReporter implements ObservationFailureReporterInterface
{
    /** @var list<ObservationDiagnostic> */
    private array $diagnostics = [];

    public function report(ObservationFailure $failure): void
    {
        $this->diagnostics[] = ObservationDiagnostic::fromFailure($failure);
    }

    /** @return list<ObservationDiagnostic> */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }

    public function count(): int
    {
        return count($this->diagnostics);
    }

    public function isEmpty(): bool
    {
        return $this->diagnostics === [];
    }

    public function clear(): void
    {
        $this->diagnostics = [];
    }
}
