<?php

declare(strict_types=1);

namespace Sif\Builder\Application\Repository;

use InvalidArgumentException;
use Sif\Builder\Repository\RepositoryIndexer;
use Sif\Builder\Repository\RepositoryIndexWriterInterface;
use Sif\Builder\Repository\RepositoryStatistics;

final readonly class GenerateRepositoryIndexService
{
    public function __construct(
        private RepositoryIndexer $indexer,
        private RepositoryIndexWriterInterface $writer,
    ) {
    }

    public function generate(string $repositoryRoot, string $outputFile): GenerateRepositoryIndexResult
    {
        if (trim($repositoryRoot) === '') {
            throw new InvalidArgumentException('Repository root must not be empty.');
        }

        if (trim($outputFile) === '') {
            throw new InvalidArgumentException('Repository index output file must not be empty.');
        }

        $indexing = $this->indexer->index($repositoryRoot);
        $statistics = new RepositoryStatistics($indexing->index);

        $this->writer->write($indexing->index, $statistics, $outputFile);

        return new GenerateRepositoryIndexResult($indexing, $statistics, $outputFile);
    }
}
