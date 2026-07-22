<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\ReferenceIntegrity;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\ReferenceIntegrity\ReferenceIntegrityInspector;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceType;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceIntegrityInspectorTest extends TestCase
{
    public function testReportsBrokenCycleSelfAndDuplicateRelationshipsDeterministically(): void
    {
        $index = new RepositoryIndex();
        $adr = new RepositoryIndexEntry('ADR-001', 'Decision', 'Governance', 'Architecture Decision Record', 'Approved', '1.0.0', 'engineering/ADR-001.md');
        $rfc = new RepositoryIndexEntry('RFC-001', 'Proposal', 'Governance', 'Request for Comments', 'Draft', '1.0.0', 'engineering/RFC-001.md');
        $index->add($adr);
        $index->add($rfc);

        $aToB1 = new Reference('ADR-001', 'RFC-001', ReferenceType::REFERENCE, 10);
        $aToB2 = new Reference('ADR-001', 'RFC-001', ReferenceType::REFERENCE, 20);
        $bToA = new Reference('RFC-001', 'ADR-001');
        $self = new Reference('ADR-001', 'ADR-001');
        $broken = new Reference('RFC-001', 'SPEC-999');

        $resolution = new ResolutionResult(
            [
                new ResolvedReference($aToB1, $rfc),
                new ResolvedReference($aToB2, $rfc),
                new ResolvedReference($bToA, $adr),
                new ResolvedReference($self, $adr),
            ],
            [new BrokenReference($broken)],
        );

        $findings = (new ReferenceIntegrityInspector())->inspect($index, $resolution);
        $codes = array_map(static fn ($finding): string => $finding->code, $findings);

        self::assertContains('REFINT-201', $codes);
        self::assertContains('REFINT-202', $codes);
        self::assertContains('REFINT-203', $codes);
        self::assertContains('REFINT-204', $codes);
        self::assertEquals($findings, (new ReferenceIntegrityInspector())->inspect($index, $resolution));
    }
}
