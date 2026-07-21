<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Repository;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Repository\RepositoryIndex;

final class RepositoryWorkspaceTest extends TestCase
{
    public function testContextCarriesWorkspaceWithoutBreakingIndexAccessor(): void
    {
        $workspace = (new RepositoryWorkspace())
            ->withMetadataRegistry(new MetadataRegistry())
            ->withIndexing(new RepositoryIndex(), new ReferenceCollection(), new ResolutionResult());

        $context = (new BuilderContext('run-001', 'D:/SIF', 'default'))
            ->withRepositoryWorkspace($workspace);

        self::assertNotNull($context->repositoryWorkspace());
        self::assertNotNull($context->repositoryIndex());
        self::assertTrue($context->repositoryIndex()?->isEmpty());
    }
}
