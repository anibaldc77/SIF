<?php
declare(strict_types=1);

namespace Sif\Support\ValueObjects;

use Sif\Support\Contracts\StringableInterface;
use Sif\Support\Exceptions\InvalidPathException;

final readonly class Path implements StringableInterface
{
    private function __construct(private string $value) {}
    public static function fromString(string $path): self
    {
        if ($path === '' || str_contains($path, "\0")) { throw new InvalidPathException('Path is empty or contains a null byte.'); }
        $absolute = str_starts_with(str_replace('\\', '/', $path), '/'); $parts = [];
        foreach (explode('/', str_replace('\\', '/', $path)) as $part) { if ($part === '' || $part === '.') { continue; } if ($part === '..') { if ($parts === []) { throw new InvalidPathException('Path escapes its root.'); } array_pop($parts); continue; } $parts[] = $part; }
        return new self(($absolute ? '/' : '').implode('/', $parts) ?: ($absolute ? '/' : '.'));
    }
    public function join(string $path): self { return self::fromString($this->value.'/'.$path); }
    public function basename(): string { return basename($this->value); }
    public function toString(): string { return $this->value; }
    public function __toString(): string { return $this->toString(); }
}
