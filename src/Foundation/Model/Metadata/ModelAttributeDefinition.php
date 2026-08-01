<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Metadata;

use Sif\Foundation\Model\Exceptions\InvalidModelAttributeDefinitionException;

final readonly class ModelAttributeDefinition
{
    public function __construct(
        private ModelAttributeName $name,
        private ModelAttributeCast $cast = ModelAttributeCast::Mixed,
        private bool $nullable = true,
        private bool $fillable = false,
        private bool $hidden = false,
        private bool $readOnly = false,
    ) {
        if ($this->fillable && $this->readOnly) {
            throw new InvalidModelAttributeDefinitionException(
                sprintf(
                    'Attribute "%s" cannot be both fillable and read-only.',
                    $this->name->value(),
                ),
            );
        }
    }

    public function name(): ModelAttributeName
    {
        return $this->name;
    }

    public function cast(): ModelAttributeCast
    {
        return $this->cast;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    public function fillable(): bool
    {
        return $this->fillable;
    }

    public function hidden(): bool
    {
        return $this->hidden;
    }

    public function readOnly(): bool
    {
        return $this->readOnly;
    }

    /** @return array<string, scalar> */
    public function summary(): array
    {
        return [
            'name' => $this->name->value(),
            'cast' => $this->cast->value,
            'nullable' => $this->nullable,
            'fillable' => $this->fillable,
            'hidden' => $this->hidden,
            'read_only' => $this->readOnly,
        ];
    }
}
