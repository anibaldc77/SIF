<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Artifact;

use Countable;
use IteratorAggregate;
use Sif\Builder\Engine\Exception\ArtifactPathCollisionException;
use Traversable;

/** @implements IteratorAggregate<int, GeneratedArtifact> */
final class ArtifactCollection implements Countable, IteratorAggregate
{
    /** @var array<string, GeneratedArtifact> */
    private array $artifacts = [];

    /** @param iterable<GeneratedArtifact> $artifacts */
    public function __construct(iterable $artifacts = [])
    {
        foreach ($artifacts as $artifact) {
            $this->add($artifact);
        }
    }

    public function add(GeneratedArtifact $artifact): void
    {
        $key = strtolower($artifact->relativePath);
        if (isset($this->artifacts[$key])) {
            throw new ArtifactPathCollisionException(sprintf(
                'Artifact path "%s" is claimed by both "%s" and "%s".',
                $artifact->relativePath,
                $this->artifacts[$key]->generator,
                $artifact->generator,
            ));
        }
        $this->artifacts[$key] = $artifact;
        ksort($this->artifacts);
    }

    public function merge(self $other): self
    {
        $merged = new self($this->artifacts);
        foreach ($other->artifacts as $artifact) {
            $merged->add($artifact);
        }

        return $merged;
    }

    /** @return list<GeneratedArtifact> */
    public function all(): array
    {
        return array_values($this->artifacts);
    }

    public function count(): int
    {
        return count($this->artifacts);
    }

    public function getIterator(): Traversable
    {
        yield from $this->all();
    }
}
