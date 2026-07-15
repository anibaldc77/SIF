<?php
declare(strict_types=1);
namespace Sif\Builder\FileSystem\Support;
use Sif\Builder\FileSystem\Exceptions\PathException;
final class PathValidator
{
    public function assertFileName(string $name): void
    {
        if ($name === '' || $name === '.' || $name === '..' || str_contains($name, '/') || str_contains($name, '\\') || str_contains($name, "\0")) { throw new PathException('File name is invalid.'); }
    }
}
