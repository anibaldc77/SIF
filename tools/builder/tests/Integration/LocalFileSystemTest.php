<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sif\Builder\FileSystem\Drivers\LocalFileSystem;
use Sif\Builder\FileSystem\Services\TemplateRenderer;
use Sif\Builder\FileSystem\Support\PathNormalizer;
use Sif\Builder\FileSystem\Support\PathValidator;

final class LocalFileSystemTest extends TestCase
{
    private string $root;
    private LocalFileSystem $filesystem;
    protected function setUp(): void { $this->root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sif-fs-'.bin2hex(random_bytes(6)); $this->filesystem = new LocalFileSystem(new PathNormalizer(), new PathValidator(), new TemplateRenderer(), $this->root); }
    protected function tearDown(): void { if (is_dir($this->root)) { $this->filesystem->deleteDirectory('.'); } }
    public function testPerformsLocalRoundTrip(): void { $this->filesystem->write('output/a.txt', 'content'); $this->filesystem->copy('output/a.txt', 'output/b.txt'); self::assertSame('content', $this->filesystem->read('output/b.txt')); self::assertCount(2, iterator_to_array($this->filesystem->files('output'))); self::assertSame(7, $this->filesystem->size('output/a.txt')); }
}
