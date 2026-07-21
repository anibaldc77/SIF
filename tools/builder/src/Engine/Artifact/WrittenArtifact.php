<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Artifact;

final readonly class WrittenArtifact
{
    public function __construct(
        public GeneratedArtifact $artifact,
        public string $absolutePath,
        public string $checksum,
    ) {
    }
}
