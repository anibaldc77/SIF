<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Metadata;

use Sif\Foundation\Model\Exceptions\InvalidModelMetadataException;

final readonly class ModelMetadata
{
    /** @var array<string, ModelAttributeDefinition> */
    private array $attributes;

    /**
     * @param class-string $modelClass
     * @param iterable<ModelAttributeDefinition> $attributes
     */
    public function __construct(
        private string $modelClass,
        private string $repositoryName,
        iterable $attributes,
        private ModelIdentityDefinition $identity,
        private ?ModelAttributeName $createdAt = null,
        private ?ModelAttributeName $updatedAt = null,
        private ?ModelAttributeName $deletedAt = null,
    ) {
        if (!class_exists($modelClass)) {
            throw new InvalidModelMetadataException(
                sprintf('Model class "%s" does not exist.', $modelClass),
            );
        }

        if (trim($repositoryName) === '') {
            throw new InvalidModelMetadataException(
                'Repository name cannot be empty.',
            );
        }

        $normalized = [];
        foreach ($attributes as $attribute) {
            $name = $attribute->name()->value();
            if (isset($normalized[$name])) {
                throw new InvalidModelMetadataException(
                    sprintf('Duplicate model attribute "%s".', $name),
                );
            }

            $normalized[$name] = $attribute;
        }

        if ($normalized === []) {
            throw new InvalidModelMetadataException(
                'Model metadata must define at least one attribute.',
            );
        }

        foreach ($identity->names() as $name) {
            if (!isset($normalized[$name])) {
                throw new InvalidModelMetadataException(
                    sprintf('Identity attribute "%s" is not defined.', $name),
                );
            }
        }

        foreach ([$createdAt, $updatedAt, $deletedAt] as $managedAttribute) {
            if ($managedAttribute !== null && !isset($normalized[$managedAttribute->value()])) {
                throw new InvalidModelMetadataException(
                    sprintf(
                        'Managed attribute "%s" is not defined.',
                        $managedAttribute->value(),
                    ),
                );
            }
        }

        $this->attributes = $normalized;
    }

    /** @return class-string */
    public function modelClass(): string
    {
        return $this->modelClass;
    }

    public function repositoryName(): string
    {
        return $this->repositoryName;
    }

    public function identity(): ModelIdentityDefinition
    {
        return $this->identity;
    }

    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function attribute(string $name): ModelAttributeDefinition
    {
        if (!isset($this->attributes[$name])) {
            throw new InvalidModelMetadataException(
                sprintf('Unknown model attribute "%s".', $name),
            );
        }

        return $this->attributes[$name];
    }

    /** @return array<string, ModelAttributeDefinition> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /** @return list<string> */
    public function fillableAttributes(): array
    {
        return $this->selectAttributeNames(
            static fn (ModelAttributeDefinition $attribute): bool => $attribute->fillable(),
        );
    }

    /** @return list<string> */
    public function hiddenAttributes(): array
    {
        return $this->selectAttributeNames(
            static fn (ModelAttributeDefinition $attribute): bool => $attribute->hidden(),
        );
    }

    public function createdAt(): ?ModelAttributeName
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?ModelAttributeName
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?ModelAttributeName
    {
        return $this->deletedAt;
    }

    public function usesSoftDeletes(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * @param callable(ModelAttributeDefinition): bool $predicate
     * @return list<string>
     */
    private function selectAttributeNames(callable $predicate): array
    {
        $selected = [];
        foreach ($this->attributes as $name => $attribute) {
            if ($predicate($attribute)) {
                $selected[] = $name;
            }
        }

        return $selected;
    }
}
