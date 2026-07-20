<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Repository\MarkdownRepositoryIndexWriter;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;
use Sif\Builder\Repository\RepositoryStatistics;

final class MarkdownRepositoryIndexWriterTest extends TestCase
{
    public function testRendersEmptyIndex(): void
    {
        $index = new RepositoryIndex();
        $content = (new MarkdownRepositoryIndexWriter())->render($index, new RepositoryStatistics($index));

        self::assertStringContainsString('# Engineering Repository Index', $content);
        self::assertStringContainsString('- Total documents: 0', $content);
        self::assertStringContainsString('| — | No documents indexed |', $content);
    }

    public function testRendersEntriesDeterministicallyAndNormalizesPaths(): void
    {
        $index = new RepositoryIndex();
        $index->add($this->entry('WP-101', 'Índice de Ingeniería', 'D:\\SIF\\engineering\\WP-101.md'));
        $index->add($this->entry('ADR-001', 'Decisión | principal', '/repo/ADR-001.md'));
        $writer = new MarkdownRepositoryIndexWriter();
        $statistics = new RepositoryStatistics($index);

        $first = $writer->render($index, $statistics);
        $second = $writer->render($index, $statistics);

        self::assertSame($first, $second);
        
        $adrPosition = strpos($first, '| ADR-001 |');
        $wpPosition = strpos($first, '| WP-101 |');

        self::assertNotFalse($adrPosition);
        self::assertNotFalse($wpPosition);
        self::assertLessThan($wpPosition, $adrPosition);
        self::assertStringContainsString('Índice de Ingeniería', $first);
        self::assertStringContainsString('Decisión \\| principal', $first);
        self::assertStringContainsString('`D:/SIF/engineering/WP-101.md`', $first);
        self::assertStringNotContainsString('Generated at', $first);
    }

    public function testWritesFileAndCreatesParentDirectory(): void
    {
        $directory = sys_get_temp_dir() . '/sif-builder-' . bin2hex(random_bytes(6));
        $outputFile = $directory . '/engineering/INDEX.generated.md';
        $index = new RepositoryIndex();
        $index->add($this->entry('WP-101', 'Repository Index', '/repo/WP-101.md'));

        try {
            (new MarkdownRepositoryIndexWriter())->write($index, new RepositoryStatistics($index), $outputFile);

            self::assertFileExists($outputFile);
            self::assertStringContainsString('| WP-101 | Repository Index |', (string) file_get_contents($outputFile));
            self::assertFileDoesNotExist($outputFile . '.tmp');
        } finally {
            $this->removeDirectory($directory);
        }
    }

    private function entry(string $identifier, string $title, string $path): RepositoryIndexEntry
    {
        return new RepositoryIndexEntry(
            identifier: $identifier,
            title: $title,
            documentClass: 'WorkPackageDocument',
            category: 'Work Package',
            status: 'Draft',
            version: '0.1.0',
            path: $path,
            workPackage: 'WP-101',
        );
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }
}
