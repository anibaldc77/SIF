<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim;
final readonly class ScimError
{
    /** @param list<ScimSchemaUri> $schemas */
    public function __construct(
        private array $schemas,
        private string $status,
        private ?string $detail = null,
        private ?string $scimType = null
    ) {}
    /** @return list<ScimSchemaUri> */
    public function schemas(): array { return $this->schemas; }
    public function status(): string { return $this->status; }
    public function detail(): ?string { return $this->detail; }
    public function scimType(): ?string { return $this->scimType; }
}
