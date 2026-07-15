<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sif\Builder\FileSystem\DTO\TemplateContext;
use Sif\Builder\FileSystem\Drivers\VirtualFileSystem;
use Sif\Builder\FileSystem\Services\TemplateRenderer;
use Sif\Builder\FileSystem\Support\PathNormalizer;
use Sif\Builder\FileSystem\Support\PathValidator;

final class VirtualFileSystemTest extends TestCase
{
    private VirtualFileSystem $filesystem;
    protected function setUp(): void { $this->filesystem = new VirtualFileSystem(new PathNormalizer(), new PathValidator(), new TemplateRenderer()); }
    public function testWritesReadsAppendsAndDescribesAFile(): void { $this->filesystem->write('build/a.txt', 'one'); $this->filesystem->append('build/a.txt', ' two'); self::assertSame('one two', $this->filesystem->read('build/a.txt')); self::assertSame(7, $this->filesystem->size('build/a.txt')); self::assertSame('text/plain', $this->filesystem->mime('build/a.txt')->value); }
    public function testCopiesMovesRenamesAndDeletes(): void { $this->filesystem->write('a.txt', 'x'); $this->filesystem->copy('a.txt', 'b.txt'); $this->filesystem->move('b.txt', 'c.txt'); $this->filesystem->rename('c.txt', 'd.txt'); $this->filesystem->delete('d.txt'); self::assertFalse($this->filesystem->exists('d.txt')); self::assertSame('x', $this->filesystem->read('a.txt')); }
    public function testMirrorsAndListsTree(): void { $this->filesystem->write('source/child/a.txt', 'x'); $this->filesystem->mirror('source', 'destination'); self::assertSame('x', $this->filesystem->read('destination/child/a.txt')); self::assertCount(1, iterator_to_array($this->filesystem->files('destination', true))); self::assertCount(1, iterator_to_array($this->filesystem->directories('destination', true))); }
    public function testRendersChecksumAndRelativePath(): void { $this->filesystem->write('view.txt', 'Hello {{name}}'); $this->filesystem->write('a/b.txt', 'body'); self::assertSame('Hello SIF', $this->filesystem->render('view.txt', (new TemplateContext())->with('name', 'SIF'))); self::assertSame('sha256', $this->filesystem->checksum('a/b.txt')->algorithm); self::assertSame('../c.txt', $this->filesystem->relative('a/b', 'a/c.txt')); }
}
