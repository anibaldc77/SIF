<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Resources\Exceptions\DuplicateResourceRootException;
use Sif\Foundation\Resources\Exceptions\InvalidResourceRootException;
use Sif\Foundation\Resources\Exceptions\ResourceFileNotFoundException;
use Sif\Foundation\Resources\Exceptions\ResourcePathEscapeException;
use Sif\Foundation\Resources\Exceptions\ResourceRootNotFoundException;
use Sif\Foundation\Resources\Filesystem\AuthorizedResourceRoot;
use Sif\Foundation\Resources\Filesystem\AuthorizedResourceRootCollection;
use Sif\Foundation\Resources\Filesystem\SafeFilesystemResourceResolver;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourceRootIdentifier;

final class ResourceFilesystemResolutionTest extends TestCase
{
    private string $temporaryDirectory;

    protected function setUp(): void
    {
        $this->temporaryDirectory = sys_get_temp_dir() . '/sif-resource-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->temporaryDirectory, 0777, true));
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->temporaryDirectory);
    }

    public function testRootIdentifierIsPortableAndCaseSensitive(): void
    {
        $identifier = new ResourceRootIdentifier(' Public.Assets ');

        self::assertSame('Public.Assets', $identifier->value());
        self::assertFalse($identifier->equals(new ResourceRootIdentifier('public.assets')));
    }

    public function testRootIdentifierRejectsPathSyntax(): void
    {
        $this->expectException(InvalidResourceRootException::class);
        new ResourceRootIdentifier('../public');
    }

    public function testAuthorizedRootRequiresExistingDirectory(): void
    {
        $this->expectException(InvalidResourceRootException::class);
        new AuthorizedResourceRoot(new ResourceRootIdentifier('missing'), $this->temporaryDirectory . '/missing');
    }

    public function testAuthorizedRootCanonicalizesItsPath(): void
    {
        $nested = $this->temporaryDirectory . '/public';
        self::assertTrue(mkdir($nested));

        $root = new AuthorizedResourceRoot(new ResourceRootIdentifier('public'), $nested . '/.');

        self::assertSame(str_replace('\\', '/', (string) realpath($nested)), $root->canonicalPath());
    }

    public function testCollectionRejectsDuplicateIdentifiers(): void
    {
        $collection = new AuthorizedResourceRootCollection();
        $identifier = new ResourceRootIdentifier('public');
        $collection->add(new AuthorizedResourceRoot($identifier, $this->temporaryDirectory));

        $this->expectException(DuplicateResourceRootException::class);
        $collection->add(new AuthorizedResourceRoot($identifier, $this->temporaryDirectory));
    }

    public function testCollectionReportsUnknownRoot(): void
    {
        $this->expectException(ResourceRootNotFoundException::class);
        (new AuthorizedResourceRootCollection())->get(new ResourceRootIdentifier('unknown'));
    }

    public function testResolverReturnsCanonicalConfinedFile(): void
    {
        $assets = $this->temporaryDirectory . '/assets';
        self::assertTrue(mkdir($assets));
        self::assertSame(3, file_put_contents($assets . '/app.css', 'css'));

        $roots = new AuthorizedResourceRootCollection();
        $roots->add(new AuthorizedResourceRoot(new ResourceRootIdentifier('public'), $this->temporaryDirectory));

        $resolved = (new SafeFilesystemResourceResolver($roots))->resolve(
            new ResourceRootIdentifier('public'),
            new ResourcePath('assets/app.css'),
        );

        self::assertSame('public', $resolved->root()->value());
        self::assertSame('assets/app.css', $resolved->relativePath()->value());
        self::assertSame('app.css', $resolved->basename());
        self::assertSame(str_replace('\\', '/', (string) realpath($assets . '/app.css')), $resolved->canonicalPath());
    }

    public function testResolverRejectsMissingFile(): void
    {
        $roots = new AuthorizedResourceRootCollection();
        $roots->add(new AuthorizedResourceRoot(new ResourceRootIdentifier('public'), $this->temporaryDirectory));

        $this->expectException(ResourceFileNotFoundException::class);
        (new SafeFilesystemResourceResolver($roots))->resolve(
            new ResourceRootIdentifier('public'),
            new ResourcePath('missing.css'),
        );
    }

    public function testResolverRejectsDirectoryAsResourceFile(): void
    {
        self::assertTrue(mkdir($this->temporaryDirectory . '/assets'));
        $roots = new AuthorizedResourceRootCollection();
        $roots->add(new AuthorizedResourceRoot(new ResourceRootIdentifier('public'), $this->temporaryDirectory));

        $this->expectException(ResourceFileNotFoundException::class);
        (new SafeFilesystemResourceResolver($roots))->resolve(
            new ResourceRootIdentifier('public'),
            new ResourcePath('assets'),
        );
    }

    public function testResolverRejectsSymbolicLinkEscapeWhenSupported(): void
    {
        $rootPath = $this->temporaryDirectory . '/root';
        $outsidePath = $this->temporaryDirectory . '/outside';
        self::assertTrue(mkdir($rootPath));
        self::assertTrue(mkdir($outsidePath));
        self::assertSame(6, file_put_contents($outsidePath . '/secret.txt', 'secret'));

        if (!@symlink($outsidePath, $rootPath . '/linked')) {
            self::markTestSkipped('Symbolic links are not available in this environment.');
        }

        $roots = new AuthorizedResourceRootCollection();
        $roots->add(new AuthorizedResourceRoot(new ResourceRootIdentifier('public'), $rootPath));

        $this->expectException(ResourcePathEscapeException::class);
        (new SafeFilesystemResourceResolver($roots))->resolve(
            new ResourceRootIdentifier('public'),
            new ResourcePath('linked/secret.txt'),
        );
    }

    public function testResolverAllowsSymbolicLinkThatRemainsInsideRootWhenSupported(): void
    {
        $rootPath = $this->temporaryDirectory . '/root';
        self::assertTrue(mkdir($rootPath));
        self::assertTrue(mkdir($rootPath . '/actual'));
        self::assertSame(2, file_put_contents($rootPath . '/actual/app.js', 'js'));

        if (!@symlink($rootPath . '/actual', $rootPath . '/linked')) {
            self::markTestSkipped('Symbolic links are not available in this environment.');
        }

        $roots = new AuthorizedResourceRootCollection();
        $roots->add(new AuthorizedResourceRoot(new ResourceRootIdentifier('public'), $rootPath));
        $resolved = (new SafeFilesystemResourceResolver($roots))->resolve(
            new ResourceRootIdentifier('public'),
            new ResourcePath('linked/app.js'),
        );

        self::assertSame(str_replace('\\', '/', (string) realpath($rootPath . '/actual/app.js')), $resolved->canonicalPath());
    }

    private function removeTree(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }

        if (is_link($path) || is_file($path)) {
            @unlink($path);
            return;
        }

        $entries = scandir($path);
        if ($entries !== false) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $this->removeTree($path . '/' . $entry);
            }
        }

        @rmdir($path);
    }
}
