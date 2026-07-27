<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Exceptions\InvalidPaginationException;

final readonly class Pagination
{
    public function __construct(
        private int $page,
        private int $perPage,
    ) {
        if ($this->page < 1) {
            throw new InvalidPaginationException(
                'Pagination page must be greater than or equal to 1.',
            );
        }

        if ($this->perPage < 1) {
            throw new InvalidPaginationException(
                'Pagination per-page value must be greater than or equal to 1.',
            );
        }
    }

    public static function firstPage(int $perPage): self
    {
        return new self(1, $perPage);
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function next(): self
    {
        return new self($this->page + 1, $this->perPage);
    }
}
