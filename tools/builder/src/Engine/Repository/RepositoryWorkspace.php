<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Repository;

use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Repository\RepositoryIndex;

final readonly class RepositoryWorkspace
{
    public function __construct(
        private ?MetadataRegistry $metadataRegistry = null,
        private ?RepositoryIndex $repositoryIndex = null,
        private ?ReferenceCollection $references = null,
        private ?ResolutionResult $resolution = null,
    ) {
    }

    public function metadataRegistry(): ?MetadataRegistry
    {
        return $this->metadataRegistry === null ? null : clone $this->metadataRegistry;
    }

    public function repositoryIndex(): ?RepositoryIndex
    {
        return $this->repositoryIndex === null ? null : clone $this->repositoryIndex;
    }

    public function references(): ?ReferenceCollection
    {
        return $this->references === null ? null : clone $this->references;
    }

    public function resolution(): ?ResolutionResult
    {
        return $this->resolution;
    }

    public function withMetadataRegistry(MetadataRegistry $registry): self
    {
        return new self($registry, $this->repositoryIndex, $this->references, $this->resolution);
    }

    public function withIndexing(
        RepositoryIndex $index,
        ReferenceCollection $references,
        ResolutionResult $resolution,
    ): self {
        return new self($this->metadataRegistry, $index, $references, $resolution);
    }
}
