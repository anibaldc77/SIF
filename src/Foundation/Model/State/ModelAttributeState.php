<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\State;

use Sif\Foundation\Model\Casting\ModelAttributeCaster;
use Sif\Foundation\Model\Exceptions\ModelHydrationException;
use Sif\Foundation\Model\Metadata\ModelMetadata;

final class ModelAttributeState
{
    /** @var array<string, mixed> */
    private array $current = [];

    /** @var array<string, mixed> */
    private array $original = [];

    public function __construct(
        private readonly ModelMetadata $metadata,
        private readonly ModelAttributeCaster $caster = new ModelAttributeCaster(),
    ) {
    }

    /** @param array<string, mixed> $attributes */
    public function hydrate(array $attributes): void
    {
        $hydrated = [];
        foreach ($attributes as $name => $value) {
            if (!$this->metadata->hasAttribute($name)) {
                throw new ModelHydrationException(sprintf('Cannot hydrate unknown model attribute "%s".', $name));
            }

            $hydrated[$name] = $this->caster->cast($this->metadata->attribute($name), $value);
        }

        $this->current = $hydrated;
        $this->original = $this->copyValues($hydrated);
    }

    /** @param array<string, mixed> $attributes */
    public function fill(array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $definition = $this->metadata->attribute($name);
            if (!$definition->fillable()) {
                throw new ModelHydrationException(sprintf('Attribute "%s" is not mass assignable.', $name));
            }

            $this->current[$name] = $this->caster->cast($definition, $value);
        }
    }

    public function set(string $name, mixed $value): void
    {
        $definition = $this->metadata->attribute($name);
        if ($definition->readOnly()) {
            throw new ModelHydrationException(sprintf('Attribute "%s" is read-only.', $name));
        }

        $this->current[$name] = $this->caster->cast($definition, $value);
    }

    public function setManaged(string $name, mixed $value): void
    {
        $definition = $this->metadata->attribute($name);
        $this->current[$name] = $this->caster->cast($definition, $value);
    }

    public function get(string $name): mixed
    {
        $this->metadata->attribute($name);

        return $this->current[$name] ?? null;
    }

    public function has(string $name): bool
    {
        $this->metadata->attribute($name);

        return array_key_exists($name, $this->current);
    }

    public function isDirty(?string $name = null): bool
    {
        if ($name !== null) {
            $this->metadata->attribute($name);

            return !$this->valuesEqual($this->current[$name] ?? null, $this->original[$name] ?? null)
                || array_key_exists($name, $this->current) !== array_key_exists($name, $this->original);
        }

        return $this->dirty() !== [];
    }

    /** @return array<string, mixed> */
    public function dirty(): array
    {
        $dirty = [];
        foreach ($this->metadata->attributes() as $name => $_definition) {
            if ($this->isDirty($name)) {
                $dirty[$name] = $this->current[$name] ?? null;
            }
        }

        return $dirty;
    }

    public function syncOriginal(): void
    {
        $this->original = $this->copyValues($this->current);
    }

    /** @return array<string, mixed> */
    public function current(): array
    {
        return $this->copyValues($this->current);
    }

    /** @return array<string, mixed> */
    public function original(): array
    {
        return $this->copyValues($this->original);
    }

/**
 * @param array<string, mixed> $values
 *
 * @return array<string, mixed>
 */
private function copyValues(array $values): array
    {
        $copy = [];
        foreach ($values as $name => $value) {
            $copy[$name] = is_object($value) ? clone $value : $value;
        }

        return $copy;
    }

    private function valuesEqual(mixed $current, mixed $original): bool
    {
        if ($current instanceof \DateTimeInterface && $original instanceof \DateTimeInterface) {
            return $current->format('U.uP') === $original->format('U.uP');
        }

        return $current === $original;
    }
}
