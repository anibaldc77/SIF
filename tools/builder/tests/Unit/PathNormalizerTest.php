<?php
declare(strict_types=1);
namespace Sif\Builder\FileSystem\Tests\Unit;
use PHPUnit\Framework\TestCase;
use Sif\Builder\FileSystem\Exceptions\PathException;
use Sif\Builder\FileSystem\Support\PathNormalizer;
final class PathNormalizerTest extends TestCase
{
    public function testNormalizesSeparatorsAndSegments(): void { self::assertSame('a/c', (new PathNormalizer())->normalize('a\\b/../c')); }
    public function testRejectsEscapingRoot(): void { $this->expectException(PathException::class); (new PathNormalizer())->normalize('../secret'); }
}
