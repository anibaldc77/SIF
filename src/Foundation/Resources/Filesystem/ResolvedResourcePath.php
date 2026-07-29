<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Filesystem;

use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourceRootIdentifier;

final readonly class ResolvedResourcePath
{
    public function __construct(
        private ResourceRootIdentifier $root,
        private ResourcePath $relativePath,
        private string $canonicalPath,
    ) {
    }

    public function root(): ResourceRootIdentifier
    {
        return $this->root;
    }

    public function relativePath(): ResourcePath
    {
        return $this->relativePath;
    }

    public function canonicalPath(): string
    {
        return $this->canonicalPath;
    }

    public function basename(): string
    {
        return $this->relativePath->basename();
    }
}
