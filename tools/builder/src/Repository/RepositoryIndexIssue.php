<?php

declare(strict_types=1);

namespace Sif\Builder\Repository;

final readonly class RepositoryIndexIssue
{
    public function __construct(
        public string $path,
        public string $message,
    ) {
    }
}
