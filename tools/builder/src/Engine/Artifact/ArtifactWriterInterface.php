<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Artifact;

interface ArtifactWriterInterface
{
    public function write(string $outputRoot, GeneratedArtifact $artifact): WrittenArtifact;
}
