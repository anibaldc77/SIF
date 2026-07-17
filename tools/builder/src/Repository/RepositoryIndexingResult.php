<?php

declare(strict_types=1);

namespace Sif\Builder\Repository;

use InvalidArgumentException;

final readonly class RepositoryIndexingResult
{
    /** @var list<RepositoryIndexIssue> */
    public array $issues;

    /**
     * @param list<RepositoryIndexIssue> $issues
     */
    public function __construct(
        public RepositoryIndex $index,
        array $issues = [],
        public float $durationSeconds = 0.0,
    ) {
        if ($durationSeconds < 0.0) {
            throw new InvalidArgumentException('Indexing duration must not be negative.');
        }

        foreach ($issues as $issue) {
            if (!$issue instanceof RepositoryIndexIssue) {
                throw new InvalidArgumentException('Indexing issues must contain only RepositoryIndexIssue instances.');
            }
        }

        $this->issues = array_values($issues);
    }

    public function isSuccessful(): bool
    {
        return $this->issues === [];
    }

    public function indexedCount(): int
    {
        return $this->index->count();
    }

    public function issueCount(): int
    {
        return count($this->issues);
    }
}
