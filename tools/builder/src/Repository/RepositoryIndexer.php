<?php

declare(strict_types=1);

namespace Sif\Builder\Repository;

use Sif\Builder\Metadata\RepositoryScannerInterface;

final readonly class RepositoryIndexer
{
    public function __construct(
        private RepositoryScannerInterface $scanner,
        private RepositoryIndexBuilder $builder,
    ) {
    }

    public function index(string $root): RepositoryIndexingResult
    {
        $startedAt = hrtime(true);
        $scanResult = $this->scanner->scan($root);
        $index = $this->builder->build($scanResult->registry);
        $issues = [];

        foreach ($scanResult->issues as $issue) {
            $issues[] = new RepositoryIndexIssue($issue->path, $issue->message);
        }

        $durationSeconds = (hrtime(true) - $startedAt) / 1_000_000_000;

        return new RepositoryIndexingResult($index, $issues, $durationSeconds);
    }
}
