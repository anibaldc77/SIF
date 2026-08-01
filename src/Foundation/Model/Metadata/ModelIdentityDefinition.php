<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Metadata;

use Sif\Foundation\Model\Exceptions\InvalidModelIdentityDefinitionException;

final readonly class ModelIdentityDefinition
{
    /** @var list<ModelAttributeName> */
    private array $attributes;

    /** @param iterable<ModelAttributeName> $attributes */
    public function __construct(iterable $attributes)
    {
        $normalized = [];
        $seen = [];

        foreach ($attributes as $attribute) {
            $name = $attribute->value();
            if (isset($seen[$name])) {
                throw new InvalidModelIdentityDefinitionException(
                    sprintf('Duplicate identity attribute "%s".', $name),
                );
            }

            $seen[$name] = true;
            $normalized[] = $attribute;
        }

        if ($normalized === []) {
            throw new InvalidModelIdentityDefinitionException(
                'A model identity must contain at least one attribute.',
            );
        }

        $this->attributes = $normalized;
    }

    /** @return list<ModelAttributeName> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function composite(): bool
    {
        return count($this->attributes) > 1;
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_map(
            static fn (ModelAttributeName $attribute): string => $attribute->value(),
            $this->attributes,
        );
    }
}
