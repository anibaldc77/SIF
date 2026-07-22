<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\RepositoryPolicy;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\RepositoryPolicy\Policy\RequiredCategoryPolicy;
use Sif\Builder\Analyzer\RepositoryPolicy\Policy\RequiredMetadataPolicy;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyInspector;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicySet;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;

final class RepositoryPolicyInspectorTest extends TestCase
{
    public function testReportsConfiguredInstitutionalPoliciesDeterministically(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/ADR-001.md', [
            'id' => 'ADR-001',
            'category' => 'Architecture Decision Record',
            'status' => 'Approved',
        ]));
        $policies = new RepositoryPolicySet([
            new RequiredMetadataPolicy('repository.approved-summary', 'summary', status: 'Approved'),
            new RequiredCategoryPolicy('repository.constitution', 'Constitution'),
        ]);

        $inspector = new RepositoryPolicyInspector();
        $findings = $inspector->inspect($registry, $policies);

        self::assertSame(['REPPOL-201', 'REPPOL-202'], array_map(static fn ($finding): string => $finding->code, $findings));
        self::assertEquals($findings, $inspector->inspect($registry, $policies));
    }

    public function testAcceptsRepositoryThatSatisfiesConfiguredPolicies(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/CONST-001.md', [
            'id' => 'CONST-001',
            'category' => 'Constitution',
            'status' => 'Approved',
            'summary' => 'Repository constitution.',
        ]));
        $policies = new RepositoryPolicySet([
            new RequiredCategoryPolicy('repository.constitution', 'Constitution'),
            new RequiredMetadataPolicy('repository.approved-summary', 'summary', status: 'Approved'),
        ]);

        self::assertSame([], (new RepositoryPolicyInspector())->inspect($registry, $policies));
    }
}
