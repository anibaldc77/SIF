<?php

declare(strict_types=1);

namespace Sif\Builder\Application\Repository;

use Sif\Builder\Repository\RepositoryIndexingResult;
use Sif\Builder\Repository\RepositoryStatistics;

final readonly class GenerateRepositoryIndexResult
{
    public function __construct(
        public RepositoryIndexingResult $indexing,
        public RepositoryStatistics $statistics,
        public string $outputFile,
    ) {
    }
}
