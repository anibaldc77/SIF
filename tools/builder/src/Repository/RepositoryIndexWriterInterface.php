<?php

declare(strict_types=1);

namespace Sif\Builder\Repository;

interface RepositoryIndexWriterInterface
{
    public function write(
        RepositoryIndex $index,
        RepositoryStatistics $statistics,
        string $outputFile,
    ): void;
}
