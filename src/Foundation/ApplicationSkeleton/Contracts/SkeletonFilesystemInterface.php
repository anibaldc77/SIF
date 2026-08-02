<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Contracts;

use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;

interface SkeletonFilesystemInterface
{
    public function exists(ProjectPath $path): bool;

    public function isFile(ProjectPath $path): bool;

    public function isDirectory(ProjectPath $path): bool;

    public function read(ProjectPath $path): string;

    public function createDirectory(ProjectPath $path): void;

    public function write(ProjectPath $path, string $content): void;
}
