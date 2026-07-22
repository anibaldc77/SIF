<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\ReferenceIntegrity;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\ReferenceIntegrity\ReferenceIntegrityAnalyzer;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceIntegrityAnalyzerTest extends TestCase
{
    public function testReturnsPreconditionDiagnosticWithoutWorkspace(): void
    {
        $result = (new ReferenceIntegrityAnalyzer())->analyze(new BuilderContext('run-1', '.', 'default'));

        self::assertFalse($result->isSuccessful());
        self::assertSame('ANALYZER-102', $result->diagnostics->all()[0]->code);
    }

    public function testProducesErrorForBrokenReference(): void
    {
        $index = new RepositoryIndex();
        $index->add(new RepositoryIndexEntry('ADR-001', 'Decision', 'Governance', 'Architecture Decision Record', 'Approved', '1.0.0', 'engineering/ADR-001.md'));
        $reference = new Reference('ADR-001', 'RFC-999');
        $resolution = new ResolutionResult([], [new BrokenReference($reference)]);
        $workspace = (new RepositoryWorkspace())->withIndexing($index, new ReferenceCollection([$reference]), $resolution);
        $context = (new BuilderContext('run-1', '.', 'default'))->withRepositoryWorkspace($workspace);

        $result = (new ReferenceIntegrityAnalyzer())->analyze($context);

        self::assertFalse($result->isSuccessful());
        self::assertSame('REFINT-201', $result->diagnostics->all()[0]->code);
        self::assertSame(ReferenceIntegrityAnalyzer::IDENTIFIER, $result->diagnostics->all()[0]->extension);
    }
}
