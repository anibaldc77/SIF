<?php
declare(strict_types=1);

namespace Sif\Support\ValueObjects;

use Sif\Support\Contracts\StringableInterface;
use Sif\Support\Exceptions\InvalidVersionException;

final readonly class Version implements StringableInterface
{
    public function __construct(public int $major, public int $minor, public int $patch, public ?string $preRelease = null, public ?string $build = null)
    {
        if ($major < 0 || $minor < 0 || $patch < 0) { throw new InvalidVersionException('Version numbers cannot be negative.'); }
    }
    public static function fromString(string $version): self
    {
        if (preg_match('/^v?(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/', $version, $matches) !== 1) { throw new InvalidVersionException("Invalid semantic version '$version'."); }
        return new self((int) $matches[1], (int) $matches[2], (int) $matches[3], $matches[4] ?? null, $matches[5] ?? null);
    }
    public function compare(self $other): int { return version_compare($this->toString(), $other->toString()); }
    public function toString(): string { return $this->major.'.'.$this->minor.'.'.$this->patch.($this->preRelease === null ? '' : '-'.$this->preRelease).($this->build === null ? '' : '+'.$this->build); }
    public function __toString(): string { return $this->toString(); }
}
