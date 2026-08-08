<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim;
final readonly class ScimGroupMember
{
    public function __construct(
        private ScimResourceId $value,
        private ?string $display = null,
        private ?string $type = null,
        private ?string $reference = null
    ) {}
    public function value(): ScimResourceId { return $this->value; }
    public function display(): ?string { return $this->display; }
    public function type(): ?string { return $this->type; }
    public function reference(): ?string { return $this->reference; }
}
