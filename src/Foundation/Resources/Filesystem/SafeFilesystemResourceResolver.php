<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Filesystem;

use Sif\Foundation\Resources\Contracts\ResourcePathResolverInterface;
use Sif\Foundation\Resources\Exceptions\ResourceFileNotFoundException;
use Sif\Foundation\Resources\Exceptions\ResourcePathEscapeException;
use Sif\Foundation\Resources\Exceptions\UnreadableResourceFileException;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourceRootIdentifier;

final readonly class SafeFilesystemResourceResolver implements ResourcePathResolverInterface
{
    public function __construct(private AuthorizedResourceRootCollection $roots)
    {
    }

    public function resolve(ResourceRootIdentifier $root, ResourcePath $path): ResolvedResourcePath
    {
        $authorizedRoot = $this->roots->get($root);
        $candidate = $authorizedRoot->canonicalPath() . '/' . $path->value();
        $canonicalPath = realpath($candidate);

        if ($canonicalPath === false || !is_file($canonicalPath)) {
            throw new ResourceFileNotFoundException(sprintf(
                'Resource file "%s" does not exist as a regular file under root "%s".',
                $path->value(),
                $root->value(),
            ));
        }

        $canonicalPath = self::normalizeAbsolutePath($canonicalPath);

        if (!self::isConfinedTo($canonicalPath, $authorizedRoot->canonicalPath())) {
            throw new ResourcePathEscapeException(sprintf(
                'Resource path "%s" escapes authorized root "%s".',
                $path->value(),
                $root->value(),
            ));
        }

        if (!is_readable($canonicalPath)) {
            throw new UnreadableResourceFileException(sprintf(
                'Resource file "%s" under root "%s" is not readable.',
                $path->value(),
                $root->value(),
            ));
        }

        return new ResolvedResourcePath($root, $path, $canonicalPath);
    }

    private static function isConfinedTo(string $candidate, string $root): bool
    {
        $candidate = self::comparisonPath($candidate);
        $root = self::comparisonPath($root);

        if ($candidate === $root) {
            return true;
        }

        $prefix = $root === '/' ? '/' : $root . '/';

        return str_starts_with($candidate, $prefix);
    }

    private static function comparisonPath(string $path): string
    {
        $path = self::normalizeAbsolutePath($path);

        return DIRECTORY_SEPARATOR === '\\' ? strtolower($path) : $path;
    }

    private static function normalizeAbsolutePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        if ($path === '/' || preg_match('/^[A-Za-z]:\/$/D', $path) === 1) {
            return $path;
        }

        return rtrim($path, '/');
    }
}
