<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim;
final readonly class ScimUser
{
    /** @param list<ScimSchemaUri> $schemas
     *  @param array<string,mixed> $attributes */
    public function __construct(
        private array $schemas,
        private string $userName,
        private bool $active = true,
        private ?ScimResourceId $id = null,
        private ?ScimMeta $meta = null,
        private array $attributes = []
    ) {}
    /** @return list<ScimSchemaUri> */
    public function schemas(): array { return $this->schemas; }
    public function userName(): string { return $this->userName; }
    public function active(): bool { return $this->active; }
    public function id(): ?ScimResourceId { return $this->id; }
    public function meta(): ?ScimMeta { return $this->meta; }
    /** @return array<string,mixed> */
    public function attributes(): array { return $this->attributes; }
}
