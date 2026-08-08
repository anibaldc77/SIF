<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim;
final readonly class ScimGroup
{
    /** @param list<ScimSchemaUri> $schemas
     *  @param list<ScimGroupMember> $members */
    public function __construct(
        private array $schemas,
        private string $displayName,
        private array $members = [],
        private ?ScimResourceId $id = null,
        private ?ScimMeta $meta = null
    ) {}
    /** @return list<ScimSchemaUri> */
    public function schemas(): array { return $this->schemas; }
    public function displayName(): string { return $this->displayName; }
    /** @return list<ScimGroupMember> */
    public function members(): array { return $this->members; }
    public function id(): ?ScimResourceId { return $this->id; }
    public function meta(): ?ScimMeta { return $this->meta; }
}
