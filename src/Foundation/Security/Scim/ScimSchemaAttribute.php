<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim;

final readonly class ScimSchemaAttribute
{
    /**
     * @param list<string> $canonicalValues
     * @param list<ScimSchemaAttribute> $subAttributes
     */
    public function __construct(
        private string $name,
        private string $type,
        private bool $multiValued = false,
        private bool $required = false,
        private ?string $mutability = null,
        private ?string $returned = null,
        private ?string $uniqueness = null,
        private array $canonicalValues = [],
        private array $subAttributes = []
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function multiValued(): bool
    {
        return $this->multiValued;
    }

    public function required(): bool
    {
        return $this->required;
    }

    public function mutability(): ?string
    {
        return $this->mutability;
    }

    public function returned(): ?string
    {
        return $this->returned;
    }

    public function uniqueness(): ?string
    {
        return $this->uniqueness;
    }

    /** @return list<string> */
    public function canonicalValues(): array
    {
        return $this->canonicalValues;
    }

    /** @return list<ScimSchemaAttribute> */
    public function subAttributes(): array
    {
        return $this->subAttributes;
    }
}
