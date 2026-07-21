<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryIndex;

use InvalidArgumentException;

final readonly class RepositoryIndexSection
{
    /** @var list<RepositoryIndexEntryView> */
    public array $entries;

    /** @param list<RepositoryIndexEntryView> $entries */
    public function __construct(public string $documentType, array $entries)
    {
        foreach ($entries as $entry) {
            if (!$entry instanceof RepositoryIndexEntryView) {
                throw new InvalidArgumentException('Repository index sections accept only entry views.');
            }
        }

        $this->entries = array_values($entries);
    }
}
