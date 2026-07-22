<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\GeneratedArtifacts;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\GeneratedArtifacts\GeneratedArtifactCatalog;
use Sif\Builder\Analyzer\GeneratedArtifacts\GeneratedArtifactDefinition;
use Sif\Builder\Analyzer\GeneratedArtifacts\GeneratedArtifactsAnalyzer;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Metadata\MetadataRegistry;

final class GeneratedArtifactsAnalyzerTest extends TestCase
{
    public function testReturnsPreconditionDiagnosticWithoutWorkspace(): void
    {
        $result = (new GeneratedArtifactsAnalyzer())->analyze(new BuilderContext('run-1', '.', 'default'));

        self::assertFalse($result->isSuccessful());
        self::assertSame('ANALYZER-105', $result->diagnostics->all()[0]->code);
    }

    public function testPublishesMissingArtifactAsWarning(): void
    {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sif-generated-analyzer-' . bin2hex(random_bytes(6));
        mkdir($root, 0777, true);
        try {
            $workspace = (new RepositoryWorkspace())->withMetadataRegistry(new MetadataRegistry());
            $context = (new BuilderContext('run-1', $root, 'default'))->withRepositoryWorkspace($workspace);
            $analyzer = new GeneratedArtifactsAnalyzer(new GeneratedArtifactCatalog([
                new GeneratedArtifactDefinition('report.generator', 'build/report.generated.json'),
            ]));

            $result = $analyzer->analyze($context);

            self::assertTrue($result->isSuccessful());
            self::assertSame('GENART-201', $result->diagnostics->all()[0]->code);
            self::assertSame(GeneratedArtifactsAnalyzer::IDENTIFIER, $result->diagnostics->all()[0]->extension);
        } finally {
            rmdir($root);
        }
    }
}
