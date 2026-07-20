<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\BuilderRequest;
use Sif\Builder\Engine\Exception\InvalidBuilderContextException;
use Sif\Builder\Repository\RepositoryIndex;

final class BuilderContextTest extends TestCase
{
    public function testCreatesContextFromRequestAndReturnsImmutableCopies(): void
    {
        $request = new BuilderRequest('/repo', 'ci');
        $context = BuilderContext::fromRequest('run-001', $request);
        $index = new RepositoryIndex();

        $indexed = $context
            ->withPhase(BuilderPhase::INDEXING)
            ->withRepositoryIndex($index)
            ->withConfiguration(['zeta' => true, 'alpha' => 1]);

        self::assertSame(BuilderPhase::CREATED, $context->phase);
        self::assertNull($context->repositoryIndex());
        self::assertSame(BuilderPhase::INDEXING, $indexed->phase);
        self::assertEquals($index, $indexed->repositoryIndex());

        $copy = $indexed->repositoryIndex();
        self::assertNotNull($copy);
        self::assertNotSame($copy, $indexed->repositoryIndex());
        self::assertSame(['alpha' => 1, 'zeta' => true], $indexed->configuration);
    }

    public function testRejectsEmptyRunIdentifier(): void
    {
        $this->expectException(InvalidBuilderContextException::class);
        new BuilderContext('', '/repo', 'default');
    }

    public function testRejectsNestedConfigurationValues(): void
    {
        $this->expectException(InvalidBuilderContextException::class);
        new BuilderContext('run-001', '/repo', 'default', configuration: ['nested' => []]);
    }
}
