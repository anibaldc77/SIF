<?php

declare(strict_types=1);

namespace Sif\Builder\Repository;

final readonly class RepositoryStatistics
{
    /** @var array<string, int> */
    public array $byCategory;

    /** @var array<string, int> */
    public array $byStatus;

    /** @var array<string, int> */
    public array $byDocumentClass;

    /** @var array<string, int> */
    public array $byWorkPackage;

    public int $total;

    public function __construct(RepositoryIndex $index)
    {
        $byCategory = [];
        $byStatus = [];
        $byDocumentClass = [];
        $byWorkPackage = [];

        foreach ($index->all() as $entry) {
            $this->increment($byCategory, $entry->category);
            $this->increment($byStatus, $entry->status);
            $this->increment($byDocumentClass, $entry->documentClass);

            if ($entry->workPackage !== null) {
                $this->increment($byWorkPackage, $entry->workPackage);
            }
        }

        ksort($byCategory);
        ksort($byStatus);
        ksort($byDocumentClass);
        ksort($byWorkPackage);

        $this->total = $index->count();
        $this->byCategory = $byCategory;
        $this->byStatus = $byStatus;
        $this->byDocumentClass = $byDocumentClass;
        $this->byWorkPackage = $byWorkPackage;
    }

    /** @param array<string, int> $counts */
    private function increment(array &$counts, string $key): void
    {
        $key = trim($key);
        if ($key === '') {
            $key = 'Unspecified';
        }

        $counts[$key] = ($counts[$key] ?? 0) + 1;
    }
}
