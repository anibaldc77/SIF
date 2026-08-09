<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

final readonly class Entitlement
{
    /**
     * @param array<string, scalar|list<scalar>|null> $metadata
     */
    public function __construct(
        private EntitlementId $id,
        private string $name,
        private string $resource,
        private array $metadata = []
    ) {
    }

    public function id(): EntitlementId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function resource(): string
    {
        return $this->resource;
    }

    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
