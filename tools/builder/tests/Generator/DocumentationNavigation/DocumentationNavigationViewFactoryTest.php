<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\DocumentationNavigation;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\DocumentationNavigation\DocumentationNavigationViewFactory;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class DocumentationNavigationViewFactoryTest extends TestCase
{
    public function testGroupsByCategoryAndWorkPackageDeterministically(): void
    {
        $index = new RepositoryIndex();
        $index->add(new RepositoryIndexEntry('WP-105', 'Built-in Generators', 'WP', 'Engineering', 'active', '1.0.0', 'engineering/WP-105.md', workPackage: 'WP-105'));
        $index->add(new RepositoryIndexEntry('ADR-001', 'Core Architecture', 'ADR', 'Architecture', 'approved', '1.0.0', 'engineering/ADR-001.md'));

        $view = (new DocumentationNavigationViewFactory())->create(new RepositoryWorkspace(repositoryIndex: $index));

        self::assertSame(2, $view->totalDocuments);
        self::assertSame(['Architecture', 'Engineering — WP-105'], array_map(static fn ($group): string => $group->heading(), $view->groups));
        self::assertSame('ADR-001', $view->groups[0]->entries[0]->identifier);
        self::assertSame('ADR-001.md', $view->groups[0]->entries[0]->link);
    }
}
