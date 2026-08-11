<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Bitstring;

final readonly class BitstringStatusValue
{
    public function __construct(
        private int $index,
        private int $value,
        private int $statusSize
    ) {
    }

    public function index(): int
    {
        return $this->index;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function statusSize(): int
    {
        return $this->statusSize;
    }

    public function asserted(): bool
    {
        return $this->value !== 0;
    }
}
