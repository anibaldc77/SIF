<?php

declare(strict_types=1);
namespace Sif\Builder\Tests\Engine\Artifact;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Artifact\ArtifactCollection;
use Sif\Builder\Engine\Artifact\GeneratedArtifact;
use Sif\Builder\Engine\Exception\ArtifactPathCollisionException;
final class ArtifactCollectionTest extends TestCase
{
    public function testOrdersArtifactsDeterministically(): void
    {
        $c = new ArtifactCollection([new GeneratedArtifact('g','z.md','md','z'),new GeneratedArtifact('g','a.md','md','a')]);
        self::assertSame(['a.md','z.md'], array_map(fn($a)=>$a->relativePath, $c->all()));
    }
    public function testRejectsCaseInsensitiveCollision(): void
    {
        $c = new ArtifactCollection([new GeneratedArtifact('a','INDEX.md','md','a')]);
        $this->expectException(ArtifactPathCollisionException::class);
        $c->add(new GeneratedArtifact('b','index.md','md','b'));
    }
}
