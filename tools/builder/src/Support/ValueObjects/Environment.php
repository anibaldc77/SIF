<?php
declare(strict_types=1);

namespace Sif\Support\ValueObjects;

use Sif\Support\Contracts\StringableInterface;
use Sif\Support\Exceptions\InvalidArgumentException;

final readonly class Environment implements StringableInterface
{
    public function __construct(public string $name)
    {
        if (preg_match('/^[A-Z][A-Z0-9_]*$/', $name) !== 1) { throw new InvalidArgumentException('Environment name is invalid.'); }
    }
    public function toString(): string { return $this->name; }
    public function __toString(): string { return $this->toString(); }
}
