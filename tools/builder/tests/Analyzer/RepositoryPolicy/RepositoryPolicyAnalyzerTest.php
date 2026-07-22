<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\RepositoryPolicy;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\RepositoryPolicy\Policy\RequiredCategoryPolicy;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyAnalyzer;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicySet;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Metadata\MetadataRegistry;

final class RepositoryPolicyAnalyzerTest extends TestCase
{
    public function testReturnsPreconditionDiagnosticWithoutWorkspace(): void
    {
        $result = (new RepositoryPolicyAnalyzer())->analyze(new BuilderContext('run-1', '.', 'default'));
        self::assertFalse($result->isSuccessful());
        self::assertSame('ANALYZER-104', $result->diagnostics->all()[0]->code);
    }

    public function testDefaultEmptyPolicySetDoesNotImposeInstitutionalRules(): void
    {
        $workspace = (new RepositoryWorkspace())->withMetadataRegistry(new MetadataRegistry());
        $context = (new BuilderContext('run-1', '.', 'default'))->withRepositoryWorkspace($workspace);
        self::assertTrue((new RepositoryPolicyAnalyzer())->analyze($context)->isSuccessful());
    }

    public function testPublishesPolicyFindingsWithRuleContext(): void
    {
        $workspace = (new RepositoryWorkspace())->withMetadataRegistry(new MetadataRegistry());
        $context = (new BuilderContext('run-1', '.', 'default'))->withRepositoryWorkspace($workspace);
        $analyzer = new RepositoryPolicyAnalyzer(new RepositoryPolicySet([
            new RequiredCategoryPolicy('repository.constitution', 'Constitution'),
        ]));

        $diagnostic = $analyzer->analyze($context)->diagnostics->all()[0];
        self::assertSame('REPPOL-201', $diagnostic->code);
        self::assertSame(RepositoryPolicyAnalyzer::IDENTIFIER, $diagnostic->extension);
        self::assertSame('repository.constitution', $diagnostic->context['rule_id']);
    }
}
