<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Application\Repository;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Application\Repository\GenerateRepositoryIndexService;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Metadata\MetadataScanResult;
use Sif\Builder\Metadata\RepositoryScannerInterface;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexBuilder;
use Sif\Builder\Repository\RepositoryIndexer;
use Sif\Builder\Repository\RepositoryIndexWriterInterface;
use Sif\Builder\Repository\RepositoryStatistics;

final class GenerateRepositoryIndexServiceTest extends TestCase
{
    public function testOrchestratesIndexingStatisticsAndWriting(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('/repo/engineering/WP-101.md', [
            'id' => 'WP-101',
            'title' => 'Engineering Repository Index',
            'document_class' => 'WorkPackageDocument',
            'category' => 'Work Package',
            'status' => 'Draft',
            'version' => '0.1.0',
            'work_package' => 'WP-101',
            'tags' => ['builder'],
        ]));

        $scanner = new class ($registry) implements RepositoryScannerInterface {
            public function __construct(private readonly MetadataRegistry $registry)
            {
            }

            public function scan(string $root): MetadataScanResult
            {
                return new MetadataScanResult($this->registry, []);
            }
        };

        $writer = new class implements RepositoryIndexWriterInterface {
            public ?RepositoryIndex $index = null;
            public ?RepositoryStatistics $statistics = null;
            public string $outputFile = '';

            public function write(
                RepositoryIndex $index,
                RepositoryStatistics $statistics,
                string $outputFile,
            ): void {
                $this->index = $index;
                $this->statistics = $statistics;
                $this->outputFile = $outputFile;
            }
        };

        $service = new GenerateRepositoryIndexService(
            new RepositoryIndexer($scanner, new RepositoryIndexBuilder()),
            $writer,
        );

        $result = $service->generate('/repo/engineering', '/repo/engineering/INDEX.generated.md');

        self::assertSame(1, $result->indexing->indexedCount());
        self::assertSame(1, $result->statistics->total);
        self::assertSame('/repo/engineering/INDEX.generated.md', $result->outputFile);
        self::assertSame($result->indexing->index, $writer->index);
        self::assertSame($result->statistics, $writer->statistics);
        self::assertSame($result->outputFile, $writer->outputFile);
    }
}
