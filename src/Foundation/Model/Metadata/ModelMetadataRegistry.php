<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Metadata;

use Sif\Foundation\Model\Exceptions\DuplicateModelMetadataException;
use Sif\Foundation\Model\Exceptions\ModelMetadataNotFoundException;

final class ModelMetadataRegistry
{
    /** @var array<class-string, ModelMetadata> */
    private array $metadata = [];

    /** @param iterable<ModelMetadata> $metadata */
    public function __construct(iterable $metadata = [])
    {
        foreach ($metadata as $item) {
            $this->register($item);
        }
    }

    public function register(ModelMetadata $metadata): void
    {
        $class = $metadata->modelClass();
        if (isset($this->metadata[$class])) {
            throw new DuplicateModelMetadataException(
                sprintf('Metadata for model "%s" is already registered.', $class),
            );
        }

        $this->metadata[$class] = $metadata;
    }

    /** @param class-string $modelClass */
    public function has(string $modelClass): bool
    {
        return isset($this->metadata[$modelClass]);
    }

    /** @param class-string $modelClass */
    public function get(string $modelClass): ModelMetadata
    {
        if (!isset($this->metadata[$modelClass])) {
            throw new ModelMetadataNotFoundException(
                sprintf('No metadata is registered for model "%s".', $modelClass),
            );
        }

        return $this->metadata[$modelClass];
    }

    /** @return array<class-string, ModelMetadata> */
    public function all(): array
    {
        ksort($this->metadata);

        return $this->metadata;
    }

    public function count(): int
    {
        return count($this->metadata);
    }
}
