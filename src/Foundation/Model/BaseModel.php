<?php

declare(strict_types=1);

namespace Sif\Foundation\Model;

use JsonSerializable;
use Sif\Foundation\Model\Exceptions\IncompleteModelIdentityException;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Model\State\ModelAttributeState;
use Sif\Foundation\Model\State\ModelSerializer;

abstract class BaseModel implements JsonSerializable
{
    private bool $persisted = false;
    private bool $deleted = false;

    final public function __construct(
        private readonly ModelMetadata $metadata,
        private readonly ModelAttributeState $state,
        private readonly ModelSerializer $serializer = new ModelSerializer(),
    ) {
        if ($metadata->modelClass() !== static::class) {
            throw new \InvalidArgumentException(sprintf(
                'Metadata for "%s" cannot construct model "%s".',
                $metadata->modelClass(),
                static::class,
            ));
        }
    }

    final public function metadata(): ModelMetadata
    {
        return $this->metadata;
    }

    final public function state(): ModelAttributeState
    {
        return $this->state;
    }

    final public function get(string $attribute): mixed
    {
        return $this->state->get($attribute);
    }

    final public function set(string $attribute, mixed $value): static
    {
        $this->state->set($attribute, $value);

        return $this;
    }

    /** @param array<string, mixed> $attributes */
    final public function fill(array $attributes): static
    {
        $this->state->fill($attributes);

        return $this;
    }

    final public function setManagedAttribute(string $attribute, mixed $value): void
    {
        $this->state->setManaged($attribute, $value);
    }

    final public function isDirty(?string $attribute = null): bool
    {
        return $this->state->isDirty($attribute);
    }

    /** @return array<string, mixed> */
    final public function dirty(): array
    {
        return $this->state->dirty();
    }

    /** @return array<string, mixed> */
    final public function identityValues(): array
    {
        $identity = [];
        foreach ($this->metadata->identity()->names() as $name) {
            if (!$this->state->has($name)) {
                continue;
            }

            $identity[$name] = $this->state->get($name);
        }

        return $identity;
    }

    final public function hasCompleteIdentity(): bool
    {
        $values = $this->identityValues();
        foreach ($this->metadata->identity()->names() as $name) {
            if (!array_key_exists($name, $values) || $values[$name] === null) {
                return false;
            }
        }

        return true;
    }

    final public function requireCompleteIdentity(): void
    {
        if (!$this->hasCompleteIdentity()) {
            throw new IncompleteModelIdentityException(sprintf(
                'Model "%s" does not have a complete identity.',
                static::class,
            ));
        }
    }

    final public function isPersisted(): bool
    {
        return $this->persisted;
    }

    final public function isDeleted(): bool
    {
        return $this->deleted;
    }

    final public function markPersisted(): void
    {
        $this->state->syncOriginal();
        $this->persisted = true;
        $this->deleted = false;
    }

    final public function markDeleted(): void
    {
        $this->deleted = true;
        $this->persisted = false;
    }

    /** @param array<string, mixed> $attributes */
    final public function replaceFromStorage(array $attributes): void
    {
        $this->state->hydrate($attributes);
        $this->persisted = true;
        $this->deleted = false;
    }

    /** @return array<string, mixed> */
    final public function toArray(): array
    {
        return $this->serializer->publicArray($this->metadata, $this->state);
    }

    /** @return array<string, mixed> */
    final public function toStorageArray(): array
    {
        return $this->serializer->storageArray($this->metadata, $this->state);
    }

    /** @return array<string, mixed> */
    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
