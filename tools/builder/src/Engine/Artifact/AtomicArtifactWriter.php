<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Artifact;

use RuntimeException;

final class AtomicArtifactWriter implements ArtifactWriterInterface
{
    public function write(string $outputRoot, GeneratedArtifact $artifact): WrittenArtifact
    {
        $root = rtrim(str_replace('\\', '/', trim($outputRoot)), '/');
        if ($root === '') {
            throw new RuntimeException('Output root must not be empty.');
        }

        $path = $root . '/' . $artifact->relativePath;
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create artifact directory "%s".', $directory));
        }

        $temporary = $path . '.tmp.' . bin2hex(random_bytes(6));
        if (file_put_contents($temporary, $artifact->content, LOCK_EX) === false) {
            throw new RuntimeException(sprintf('Unable to write temporary artifact "%s".', $temporary));
        }

        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException(sprintf('Unable to atomically publish artifact "%s".', $path));
        }

        return new WrittenArtifact($artifact, $path, hash_file('sha256', $path) ?: $artifact->checksum());
    }
}
