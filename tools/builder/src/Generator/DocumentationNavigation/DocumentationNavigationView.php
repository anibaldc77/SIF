<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\DocumentationNavigation;

final readonly class DocumentationNavigationView
{
    /** @param list<DocumentationNavigationGroupView> $groups */
    public function __construct(
        public int $totalDocuments,
        public array $groups,
    ) {
    }
}
