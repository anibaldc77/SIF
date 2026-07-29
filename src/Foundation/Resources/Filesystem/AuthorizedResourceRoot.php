<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Filesystem;

use Sif\Foundation\Resources\Exceptions\InvalidResourceRootException;
use Sif\Foundation\Resources\ResourceRootIdentifier;

final readonly class AuthorizedResourceRoot
{
    private string $canonicalPath;

    public function __construct(
        private ResourceRootIdentifier $identifier,
        string $path,
    ) {
        if (str_contains($path, "\0")) {
            throw new InvalidResourceRootException('Authorized resource roots must not contain null bytes.');
        }

        $canonicalPath = realpath($path);
        if ($canonicalPath === false || !is_dir($canonicalPath)) {
            throw new InvalidResourceRootException(sprintf('Authorized resource root "%s" must reference an existing directory.', $path));
        }

        if (!is_readable($canonicalPath)) {
            throw new InvalidResourceRootException(sprintf('Authorized resource root "%s" must be readable.', $path));
        }

        $this->canonicalPath = self::normalizeAbsolutePath($canonicalPath);
    }

    public function identifier(): ResourceRootIdentifier
    {
        return $this->identifier;
    }

    public function canonicalPath(): string
    {
        return $this->canonicalPath;
    }

    private static function normalizeAbsolutePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        if ($path === '/') {
            return $path;
        }

        if (preg_match('/^[A-Za-z]:\/$/D', $path) === 1) {
            return $path;
        }

        return rtrim($path, '/');
    }
}
