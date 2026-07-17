<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Metadata\Exception\MetadataReadException;
use Sif\Builder\Metadata\MarkdownFrontMatterReader;

final class MarkdownFrontMatterReaderTest extends TestCase
{
    public function testReadsScalarsBlockListsAndInlineLists(): void
    {
        $path = $this->file("---\nid: ES-004\ntitle: Markdown Convention\nauthors:\n  - SIF Architecture Board\ntags: [documentation, markdown]\nsupersedes: null\n---\n# Body\n");

        $document = (new MarkdownFrontMatterReader())->read($path);

        self::assertSame('ES-004', $document->id());
        self::assertSame(['SIF Architecture Board'], $document->metadata['authors']);
        self::assertSame(['documentation', 'markdown'], $document->metadata['tags']);
        self::assertNull($document->metadata['supersedes']);
    }

    public function testRejectsDocumentWithoutFrontMatter(): void
    {
        $path = $this->file("# No metadata\n");

        $this->expectException(MetadataReadException::class);
        (new MarkdownFrontMatterReader())->read($path);
    }

    public function testRejectsDuplicateKeys(): void
    {
        $path = $this->file("---\nid: ES-004\nid: ES-005\n---\n");

        $this->expectException(MetadataReadException::class);
        (new MarkdownFrontMatterReader())->read($path);
    }

    private function file(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sif-metadata-');
        self::assertNotFalse($path);
        $markdownPath = $path . '.md';
        rename($path, $markdownPath);
        file_put_contents($markdownPath, $content);

        return $markdownPath;
    }
}
