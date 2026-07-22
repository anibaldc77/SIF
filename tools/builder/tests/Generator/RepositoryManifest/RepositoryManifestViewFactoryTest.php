<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\RepositoryManifest;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\RepositoryManifest\RepositoryManifestViewFactory;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceType;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class RepositoryManifestViewFactoryTest extends TestCase
{
    public function testBuildsDeterministicManifestWithReferenceCountsAndFingerprints(): void
    {
        $index = new RepositoryIndex();
        $wp = $this->entry('WP-105', ['generator', 'builder']);
        $adr = $this->entry('ADR-001', ['architecture']);
        $index->add($wp);
        $index->add($adr);

        $resolution = new ResolutionResult(
            resolved: [new ResolvedReference(new Reference('WP-105', 'ADR-001', ReferenceType::IMPLEMENTS), $adr)],
            broken: [new BrokenReference(new Reference('WP-105', 'SPEC-999', ReferenceType::REFERENCE), 'target_not_found')],
        );

        $view = (new RepositoryManifestViewFactory())->create(new RepositoryWorkspace(repositoryIndex: $index, resolution: $resolution));

        self::assertSame(['ADR-001', 'WP-105'], array_map(static fn ($document): string => $document->identifier, $view->documents));
        self::assertSame(1, $view->documents[0]->incomingReferences);
        self::assertSame(1, $view->documents[1]->outgoingReferences);
        self::assertSame(1, $view->documents[1]->brokenReferences);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $view->documents[0]->entryFingerprint);
        self::assertSame(['ADR' => 1, 'WP' => 1], $view->documentsByType);
        self::assertSame(1, $view->resolvedReferenceCount());
        self::assertSame(1, $view->brokenReferenceCount());
    }

    /** @param list<string> $tags */
    private function entry(string $identifier, array $tags): RepositoryIndexEntry
    {
        return new RepositoryIndexEntry($identifier, $identifier, explode('-', $identifier, 2)[0], 'engineering', 'approved', '1.0.0', 'engineering\\' . $identifier . '.md', tags: $tags);
    }
}
