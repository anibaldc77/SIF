<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use Sif\Foundation\ErrorHandling\Contracts\FailureReporterInterface;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

final class InMemoryFailureReporter implements FailureReporterInterface
{
    /** @var list<array{envelope:FailureEnvelope,decision:RecoveryDecision}> */
    private array $reports = [];

    public function report(FailureEnvelope $envelope, RecoveryDecision $decision): void
    {
        $this->reports[] = ['envelope' => $envelope, 'decision' => $decision];
    }

    /** @return list<array{envelope:FailureEnvelope,decision:RecoveryDecision}> */
    public function reports(): array
    {
        return $this->reports;
    }

    public function count(): int
    {
        return count($this->reports);
    }

    public function clear(): void
    {
        $this->reports = [];
    }
}
