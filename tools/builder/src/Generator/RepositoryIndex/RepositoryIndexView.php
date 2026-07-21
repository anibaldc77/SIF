<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryIndex;

use InvalidArgumentException;

final readonly class RepositoryIndexView
{
    /** @var list<RepositoryIndexSection> */
    public array $sections;

    /** @var array<string, int> */
    public array $byStatus;

    /** @var array<string, int> */
    public array $byType;

    /**
     * @param list<RepositoryIndexSection> $sections
     * @param array<string, int> $byStatus
     * @param array<string, int> $byType
     */
    public function __construct(
        public int $totalDocuments,
        public int $resolvedReferences,
        public int $unresolvedReferences,
        array $sections,
        array $byStatus,
        array $byType,
    ) {
        foreach ($sections as $section) {
            if (!$section instanceof RepositoryIndexSection) {
                throw new InvalidArgumentException('Repository index views accept only sections.');
            }
        }

        ksort($byStatus, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($byType, SORT_NATURAL | SORT_FLAG_CASE);

        $this->sections = array_values($sections);
        $this->byStatus = $byStatus;
        $this->byType = $byType;
    }
}
