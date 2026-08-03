<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Api;

final readonly class NegotiationResult
{
    /** @param list<MediaType> $supported */
    public function __construct(
        private ?MediaType $selected,
        private array $supported,
    ) {
    }

    /** @param list<MediaType> $supported */
    public static function accepted(MediaType $selected, array $supported): self
    {
        return new self($selected, array_values($supported));
    }

    /** @param list<MediaType> $supported */
    public static function rejected(array $supported): self
    {
        return new self(null, array_values($supported));
    }

    public function acceptable(): bool
    {
        return $this->selected !== null;
    }

    public function selected(): ?MediaType
    {
        return $this->selected;
    }

    /** @return list<MediaType> */
    public function supported(): array
    {
        return $this->supported;
    }
}
