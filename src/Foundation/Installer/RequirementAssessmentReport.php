<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidRequirementAssessmentReportException;

final readonly class RequirementAssessmentReport
{
    /** @var list<RequirementProbeResult> */
    private array $results;

    /**
     * @param list<RequirementProbeResult> $results
     */
    public function __construct(array $results)
    {
        $seen = [];

        foreach ($results as $result) {
            if (!$result instanceof RequirementProbeResult) {
                throw new InvalidRequirementAssessmentReportException(
                    'Requirement report members must be RequirementProbeResult instances.',
                );
            }

            $identifier = $result->identifier()->value();
            if (isset($seen[$identifier])) {
                throw new InvalidRequirementAssessmentReportException(
                    sprintf('Requirement report contains duplicate identifier "%s".', $identifier),
                );
            }

            $seen[$identifier] = true;
        }

        $this->results = array_values($results);
    }

    /** @return list<RequirementProbeResult> */
    public function results(): array
    {
        return $this->results;
    }

    public function canProceed(): bool
    {
        foreach ($this->results as $result) {
            if (
                !$result->passedRequirement()
                && $result->severity() === RequirementSeverity::Required
            ) {
                return false;
            }
        }

        return true;
    }

    public function hasWarnings(): bool
    {
        foreach ($this->results as $result) {
            if (
                !$result->passedRequirement()
                && $result->severity() === RequirementSeverity::Optional
            ) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->results);
    }

    /**
     * @return list<array{identifier: string, severity: string, status: string, message: string}>
     */
    public function summary(): array
    {
        return array_map(
            static fn (RequirementProbeResult $result): array => $result->summary(),
            $this->results,
        );
    }
}
