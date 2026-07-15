<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\Support;

use Sif\Builder\FileSystem\Exceptions\PathException;

final class PathNormalizer
{
    public function normalize(string $path): string
    {
        if ($path === '' || str_contains($path, "\0")) { throw new PathException('Path is empty or contains a null byte.'); }
        $path = str_replace('\\', '/', $path);
        $prefix = '';
        if (preg_match('#^([A-Za-z]:)(/|$)#', $path, $matches) === 1) { $prefix = $matches[1]; $path = substr($path, 2); }
        $absolute = str_starts_with($path, '/');
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') { continue; }
            if ($part === '..') { if ($parts === []) { throw new PathException('Path escapes its root.'); } array_pop($parts); continue; }
            $parts[] = $part;
        }
        $normalized = implode('/', $parts);
        if ($absolute) { $normalized = '/'.$normalized; }
        if ($prefix !== '') { $normalized = $prefix.($absolute ? '' : '/').$normalized; }
        return $normalized === '' ? ($absolute ? '/' : '.') : rtrim($normalized, '/');
    }
}
