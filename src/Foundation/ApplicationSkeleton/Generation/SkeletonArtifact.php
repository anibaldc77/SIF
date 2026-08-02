<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;

final readonly class SkeletonArtifact
{
    private ?string $fingerprint;

    public function __construct(
        private ProjectPathDefinition $path,
        private SkeletonArtifactType $type,
        private ?string $content = null,
    ) {
        if ($type === SkeletonArtifactType::Directory && $content !== null) {
            throw new InvalidSkeletonValueException('Directory artifacts cannot define file content.');
        }

        if ($type === SkeletonArtifactType::File && $content === null) {
            throw new InvalidSkeletonValueException('File artifacts require content.');
        }

        if ($content !== null && str_contains($content, "\r")) {
            throw new InvalidSkeletonValueException('Skeleton file content must use LF line endings.');
        }

        $this->fingerprint = $content === null ? null : hash('sha256', $content);
    }

    public function path(): ProjectPathDefinition
    {
        return $this->path;
    }

    public function type(): SkeletonArtifactType
    {
        return $this->type;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function fingerprint(): ?string
    {
        return $this->fingerprint;
    }

    /** @return array<string, string|null> */
    public function summary(): array
    {
        return [
            'path' => $this->path->path()->value(),
            'type' => $this->type->value,
            'ownership' => $this->path->ownership()->value,
            'overwrite_policy' => $this->path->overwritePolicy()->value,
            'fingerprint' => $this->fingerprint,
        ];
    }
}
