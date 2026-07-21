<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Configuration;

use Sif\Builder\Cli\Contract\PathResolverInterface;
use Sif\Builder\Cli\Exception\RequestMappingException;

final readonly class WorkingDirectoryPathResolver implements PathResolverInterface
{
    private string $workingDirectory;

    public function __construct(string $workingDirectory)
    {
        $this->workingDirectory = self::normalizeAbsolute($workingDirectory, 'Working directory');
    }

    public function resolve(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '' || str_contains($path, "\0")) {
            throw new RequestMappingException('Path must be non-empty and must not contain null bytes.');
        }

        if (!self::isAbsolute($path)) {
            $path = $this->workingDirectory . '/' . $path;
        }

        return self::collapse($path);
    }

    private static function normalizeAbsolute(string $path, string $label): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '' || str_contains($path, "\0") || !self::isAbsolute($path)) {
            throw new RequestMappingException(sprintf('%s must be an absolute path.', $label));
        }

        return self::collapse($path);
    }

    private static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path) === 1;
    }

    private static function collapse(string $path): string
    {
        $prefix = '';
        if (preg_match('/^[A-Za-z]:\//', $path) === 1) {
            $prefix = strtoupper(substr($path, 0, 2));
            $path = substr($path, 2);
        } elseif (str_starts_with($path, '/')) {
            $prefix = '/';
        }

        $segments = [];
        foreach (explode('/', ltrim($path, '/')) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                if ($segments === []) {
                    throw new RequestMappingException('Path escapes its absolute root.');
                }
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        $suffix = implode('/', $segments);
        if ($prefix === '/') {
            return '/' . $suffix;
        }

        return $suffix === '' ? $prefix . '/' : $prefix . '/' . $suffix;
    }
}
