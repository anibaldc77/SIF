<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\ReferenceReport;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\ReferenceReport\ReferenceReportViewFactory;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceType;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceReportViewFactoryTest extends TestCase
{
    public function testBuildsDeterministicStatisticsAndDocumentHealth(): void
    {
        $index = new RepositoryIndex();
        $a = $this->entry('ADR-001');
        $b = $this->entry('WP-105');
        $c = $this->entry('RFC-002');
        $index->add($c);
        $index->add($b);
        $index->add($a);

        $resolution = new ResolutionResult(
            resolved: [new ResolvedReference(new Reference('WP-105', 'ADR-001', ReferenceType::IMPLEMENTS), $a)],
            broken: [new BrokenReference(new Reference('WP-105', 'SPEC-999', ReferenceType::RELATED))],
        );

        $view = (new ReferenceReportViewFactory())->create(new RepositoryWorkspace(repositoryIndex: $index, resolution: $resolution));

        self::assertSame(3, $view->totalDocuments);
        self::assertSame(2, $view->totalReferences);
        self::assertSame(['implements' => 1, 'related' => 1], $view->byType);
        self::assertSame(['ADR-001', 'RFC-002', 'WP-105'], array_map(static fn ($item): string => $item->identifier, $view->documents));
        self::assertSame(['RFC-002'], array_map(static fn ($item): string => $item->identifier, $view->isolatedDocuments()));
        self::assertSame('ADR-001', $view->mostReferencedDocuments()[0]->identifier);
    }

    private function entry(string $identifier): RepositoryIndexEntry
    {
        return new RepositoryIndexEntry($identifier, $identifier, explode('-', $identifier, 2)[0], 'engineering', 'approved', '1.0.0', 'engineering/' . $identifier . '.md');
    }
}
