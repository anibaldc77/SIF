<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\DocumentationNavigation;

final readonly class DocumentationNavigationEntryView
{
    public function __construct(
        public string $identifier,
        public string $title,
        public string $documentType,
        public string $status,
        public string $version,
        public string $path,
        public string $link,
    ) {
    }
}
