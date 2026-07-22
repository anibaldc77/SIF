<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\DocumentationNavigation;

final readonly class DocumentationNavigationGroupView
{
    /** @param list<DocumentationNavigationEntryView> $entries */
    public function __construct(
        public string $category,
        public ?string $workPackage,
        public array $entries,
    ) {
    }

    public function heading(): string
    {
        return $this->workPackage === null
            ? $this->category
            : sprintf('%s — %s', $this->category, $this->workPackage);
    }
}
