<?php

declare(strict_types=1);
namespace Sif\Builder\Tests\Engine\Artifact;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Artifact\GeneratedArtifact;
use Sif\Builder\Engine\Exception\InvalidGeneratedArtifactException;
final class GeneratedArtifactTest extends TestCase
{
    public function testNormalizesAndSerializesArtifact(): void
    {
        $a = new GeneratedArtifact(' Docs.Index ', 'docs\\INDEX.md', ' Markdown ', 'content');
        self::assertSame('docs.index', $a->generator);
        self::assertSame('docs/INDEX.md', $a->relativePath);
        self::assertSame(hash('sha256', 'content'), $a->checksum());
    }
    /** @dataProvider invalidPaths */
    public function testRejectsUnsafePaths(string $path): void
    {
        $this->expectException(InvalidGeneratedArtifactException::class);
        new GeneratedArtifact('docs', $path, 'markdown', 'x');
    }
    public static function invalidPaths(): array { return [[''], ['/a'], ['../a'], ['a/../b'], ['C:/a']]; }
}
