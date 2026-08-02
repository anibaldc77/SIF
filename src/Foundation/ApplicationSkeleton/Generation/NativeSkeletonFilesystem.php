<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Exceptions\ApplicationSkeletonException;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;

final readonly class NativeSkeletonFilesystem implements SkeletonFilesystemInterface
{
    private string $root;

    public function __construct(string $root)
    {
        $root = rtrim($root, "\\/");
        if ($root === '' || !is_dir($root)) {
            throw new ApplicationSkeletonException('Skeleton filesystem root must be an existing directory.');
        }

        $resolved = realpath($root);
        if ($resolved === false) {
            throw new ApplicationSkeletonException('Skeleton filesystem root could not be resolved.');
        }
        $this->root = $resolved;
    }

    public function exists(ProjectPath $path): bool
    {
        return file_exists($this->resolve($path));
    }

    public function isFile(ProjectPath $path): bool
    {
        return is_file($this->resolve($path));
    }

    public function isDirectory(ProjectPath $path): bool
    {
        return is_dir($this->resolve($path));
    }

    public function read(ProjectPath $path): string
    {
        $content = @file_get_contents($this->resolve($path));
        if ($content === false) {
            throw new ApplicationSkeletonException(sprintf('Unable to read "%s".', $path->value()));
        }

        return $content;
    }

    public function createDirectory(ProjectPath $path): void
    {
        $target = $this->resolve($path);
        if (is_dir($target)) {
            return;
        }
        if (!@mkdir($target, 0775, true) && !is_dir($target)) {
            throw new ApplicationSkeletonException(sprintf('Unable to create directory "%s".', $path->value()));
        }
    }

    public function write(ProjectPath $path, string $content): void
    {
        $target = $this->resolve($path);
        $parent = dirname($target);
        if (!is_dir($parent) && !@mkdir($parent, 0775, true) && !is_dir($parent)) {
            throw new ApplicationSkeletonException(sprintf('Unable to create parent directory for "%s".', $path->value()));
        }

        if (@file_put_contents($target, $content, LOCK_EX) === false) {
            throw new ApplicationSkeletonException(sprintf('Unable to write "%s".', $path->value()));
        }
    }

    private function resolve(ProjectPath $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path->value());
    }
}
