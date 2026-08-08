<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim;

final readonly class ScimFeatureSupport
{
    public function __construct(
        private bool $supported,
        private ?int $maxOperations = null,
        private ?int $maxPayloadSize = null
    ) {
    }

    public function supported(): bool
    {
        return $this->supported;
    }

    public function maxOperations(): ?int
    {
        return $this->maxOperations;
    }

    public function maxPayloadSize(): ?int
    {
        return $this->maxPayloadSize;
    }
}
