<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim;

final readonly class ScimResourceType
{
    /**
     * @param list<ScimSchemaUri> $schemaExtensions
     */
    public function __construct(
        private string $id,
        private string $name,
        private string $endpoint,
        private ScimSchemaUri $schema,
        private array $schemaExtensions = []
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function endpoint(): string
    {
        return $this->endpoint;
    }

    public function schema(): ScimSchemaUri
    {
        return $this->schema;
    }

    /** @return list<ScimSchemaUri> */
    public function schemaExtensions(): array
    {
        return $this->schemaExtensions;
    }
}
