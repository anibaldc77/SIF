<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\RepositoryIndex;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\RepositoryIndex\RepositoryIndexViewFactory;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class RepositoryIndexViewFactoryTest extends TestCase
{
    public function testOrdersTypesIdentifiersAndNormalizesLinks(): void
    {
        $index = new RepositoryIndex();
        $index->add(new RepositoryIndexEntry('WP-200', 'Work package', 'WP', '', 'draft', '1.0', 'engineering/specifications/WP-200.md'));
        $index->add(new RepositoryIndexEntry('ADR-010', 'Decision | Ten', 'ADR', '', 'accepted', '1.0', 'engineering\\decisions\\ADR 010.md'));
        $index->add(new RepositoryIndexEntry('ADR-002', 'Decision Two', 'ADR', '', 'accepted', '1.0', 'engineering/decisions/ADR-002.md'));
        $resolution = new ResolutionResult(broken: [new BrokenReference(new Reference('WP-200', 'ADR-999'))]);
        $view = (new RepositoryIndexViewFactory())->create(new RepositoryWorkspace(repositoryIndex: $index, resolution: $resolution));

        self::assertSame(['ADR', 'WP'], array_map(static fn ($section): string => $section->documentType, $view->sections));
        self::assertSame(['ADR-002', 'ADR-010'], array_map(static fn ($entry): string => $entry->identifier, $view->sections[0]->entries));
        self::assertSame('decisions/ADR%20010.md', $view->sections[0]->entries[1]->link);
        self::assertSame(1, $view->unresolvedReferences);
    }
}
