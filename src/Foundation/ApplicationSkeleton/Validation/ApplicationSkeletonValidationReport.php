<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Validation;

final readonly class ApplicationSkeletonValidationReport
{
    /** @var list<ApplicationSkeletonValidationIssue> */
    private array $issues;

    /** @param iterable<ApplicationSkeletonValidationIssue> $issues */
    public function __construct(iterable $issues)
    {
        $normalized = [];
        foreach ($issues as $issue) {
            $normalized[] = $issue;
        }
        $this->issues = $normalized;
    }

    public function valid(): bool
    {
        return $this->issues === [];
    }

    /** @return list<ApplicationSkeletonValidationIssue> */
    public function issues(): array
    {
        return $this->issues;
    }

    /** @return array{valid: bool, issue_count: int, issues: list<array{code: string, message: string}>} */
    public function summary(): array
    {
        return [
            'valid' => $this->valid(),
            'issue_count' => count($this->issues),
            'issues' => array_map(
                static fn (ApplicationSkeletonValidationIssue $issue): array => $issue->summary(),
                $this->issues,
            ),
        ];
    }
}
