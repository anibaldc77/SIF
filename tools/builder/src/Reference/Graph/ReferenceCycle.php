<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Graph;

use InvalidArgumentException;

final readonly class ReferenceCycle
{
    /** @var list<string> */
    public array $path;

    /** @param list<string> $path */
    public function __construct(array $path)
    {
        if (count($path) < 2 || $path[0] !== $path[array_key_last($path)]) {
            throw new InvalidArgumentException('A reference cycle must contain a closed path.');
        }
        $this->path = array_values($path);
    }

    public function identity(): string
    {
        return implode('->', $this->path);
    }
}
