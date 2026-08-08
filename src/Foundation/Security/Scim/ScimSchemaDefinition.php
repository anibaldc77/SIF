<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim;

final readonly class ScimSchemaDefinition
{
    /**
     * @param list<ScimSchemaAttribute> $attributes
     */
    public function __construct(
        private ScimSchemaUri $id,
        private string $name,
        private ?string $description,
        private array $attributes
    ) {
    }

    public function id(): ScimSchemaUri
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /** @return list<ScimSchemaAttribute> */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
