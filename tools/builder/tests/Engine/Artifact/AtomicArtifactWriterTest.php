<?php

declare(strict_types=1);
namespace Sif\Builder\Tests\Engine\Artifact;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Artifact\AtomicArtifactWriter;
use Sif\Builder\Engine\Artifact\GeneratedArtifact;
final class AtomicArtifactWriterTest extends TestCase
{
    public function testWritesArtifactAndRemovesTemporaryFile(): void
    {
        $root = sys_get_temp_dir() . '/sif-artifact-' . bin2hex(random_bytes(4));
        try {
            $artifact = new GeneratedArtifact('docs','reports/index.md','markdown','hello');
            $written = (new AtomicArtifactWriter())->write($root, $artifact);
            self::assertFileExists($written->absolutePath);
            self::assertSame('hello', file_get_contents($written->absolutePath));
            self::assertSame($artifact->checksum(), $written->checksum);
            self::assertSame([], glob($written->absolutePath . '.tmp.*') ?: []);
        } finally {
            if (is_dir($root . '/reports')) { @unlink($root . '/reports/index.md'); @rmdir($root . '/reports'); }
            @rmdir($root);
        }
    }
}
