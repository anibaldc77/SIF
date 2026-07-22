<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\GeneratedArtifacts;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\GeneratedArtifacts\GeneratedArtifactCatalog;
use Sif\Builder\Analyzer\GeneratedArtifacts\GeneratedArtifactDefinition;
use Sif\Builder\Analyzer\GeneratedArtifacts\GeneratedArtifactsInspector;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;

final class GeneratedArtifactsInspectorTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sif-generated-artifacts-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/engineering', 0777, true);
        mkdir($this->root . '/build', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->remove($this->root);
    }

    public function testReportsMissingEmptyStaleAndUnexpectedArtifactsDeterministically(): void
    {
        file_put_contents($this->root . '/engineering/ADR-001.md', "---\nid: ADR-001\n---\n");
        touch($this->root . '/engineering/ADR-001.md', 200);
        file_put_contents($this->root . '/build/empty.generated.json', '');
        touch($this->root . '/build/empty.generated.json', 100);
        file_put_contents($this->root . '/build/unexpected.generated.json', '{}');

        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/ADR-001.md', ['id' => 'ADR-001']));
        $catalog = new GeneratedArtifactCatalog([
            new GeneratedArtifactDefinition('empty.generator', 'build/empty.generated.json'),
            new GeneratedArtifactDefinition('missing.generator', 'build/missing.generated.json'),
        ]);

        $inspector = new GeneratedArtifactsInspector();
        $findings = $inspector->inspect($this->root, $this->root, $registry, $catalog);

        self::assertSame(
            ['GENART-201', 'GENART-202', 'GENART-203', 'GENART-204'],
            array_map(static fn ($finding): string => $finding->code, $findings),
        );
        self::assertEquals($findings, $inspector->inspect($this->root, $this->root, $registry, $catalog));
    }

    public function testAcceptsCurrentGovernedArtifact(): void
    {
        file_put_contents($this->root . '/engineering/ADR-001.md', 'source');
        touch($this->root . '/engineering/ADR-001.md', 100);
        file_put_contents($this->root . '/build/report.generated.json', '{}');
        touch($this->root . '/build/report.generated.json', 200);

        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/ADR-001.md', ['id' => 'ADR-001']));
        $catalog = new GeneratedArtifactCatalog([
            new GeneratedArtifactDefinition('report.generator', 'build/report.generated.json'),
        ]);

        self::assertSame([], (new GeneratedArtifactsInspector())->inspect($this->root, $this->root, $registry, $catalog));
    }

    private function remove(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }
        if (is_dir($path)) {
            foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $entry) {
                $this->remove($path . DIRECTORY_SEPARATOR . $entry);
            }
            rmdir($path);
            return;
        }
        unlink($path);
    }
}
