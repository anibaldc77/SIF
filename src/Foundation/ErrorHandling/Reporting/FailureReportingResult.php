<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

final readonly class FailureReportingResult
{
    /** @var list<string> */
    private array $reported;

    /** @var list<string> */
    private array $filtered;

    /** @var list<FailureReporterFailure> */
    private array $failures;

    /**
     * @param list<string> $reported
     * @param list<string> $filtered
     * @param list<FailureReporterFailure> $failures
     */
    public function __construct(array $reported, array $filtered, array $failures)
    {
        $this->reported = array_values($reported);
        $this->filtered = array_values($filtered);
        $this->failures = array_values($failures);
    }

    /** @return list<string> */
    public function reportedRoutes(): array
    {
        return $this->reported;
    }

    /** @return list<string> */
    public function filteredRoutes(): array
    {
        return $this->filtered;
    }

    /** @return list<FailureReporterFailure> */
    public function failures(): array
    {
        return $this->failures;
    }

    public function reportedCount(): int
    {
        return count($this->reported);
    }

    public function failureCount(): int
    {
        return count($this->failures);
    }

    public function succeeded(): bool
    {
        return $this->failures === [];
    }
}
