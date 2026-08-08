<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim;
use DateTimeImmutable;
final readonly class ScimMeta
{
    public function __construct(
        private string $resourceType,
        private ?DateTimeImmutable $created = null,
        private ?DateTimeImmutable $lastModified = null,
        private ?string $version = null,
        private ?string $location = null
    ) {}
    public function resourceType(): string { return $this->resourceType; }
    public function created(): ?DateTimeImmutable { return $this->created; }
    public function lastModified(): ?DateTimeImmutable { return $this->lastModified; }
    public function version(): ?string { return $this->version; }
    public function location(): ?string { return $this->location; }
}
