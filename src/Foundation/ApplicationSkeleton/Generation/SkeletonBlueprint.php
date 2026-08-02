<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;

final readonly class SkeletonBlueprint
{
    /** @var list<SkeletonArtifact> */
    private array $artifacts;

    /** @param iterable<SkeletonArtifact> $artifacts */
    public function __construct(
        private ProjectManifest $manifest,
        iterable $artifacts,
    ) {
        $normalized = [];
        foreach ($artifacts as $artifact) {
            $path = $artifact->path()->path()->value();
            if (isset($normalized[$path])) {
                throw new InvalidSkeletonValueException(sprintf('Duplicate skeleton artifact "%s".', $path));
            }
            if (!isset($manifest->paths()[$path])) {
                throw new InvalidSkeletonValueException(
                    sprintf('Skeleton artifact "%s" is not declared by the project manifest.', $path),
                );
            }
            $normalized[$path] = $artifact;
        }

        ksort($normalized, SORT_STRING);
        $this->artifacts = array_values($normalized);
    }

    public function manifest(): ProjectManifest
    {
        return $this->manifest;
    }

    /** @return list<SkeletonArtifact> */
    public function artifacts(): array
    {
        return $this->artifacts;
    }

    public function fingerprint(): string
    {
        $json = json_encode(
            array_map(static fn (SkeletonArtifact $artifact): array => $artifact->summary(), $this->artifacts),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
        );

        return hash('sha256', $json);
    }
}
