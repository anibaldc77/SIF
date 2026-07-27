<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Contracts\ResultSetInterface;
use Sif\Foundation\Exceptions\InvalidPageResultException;

/**
 * @template T
 */
final readonly class PageResult
{
    /**
     * @param ResultSetInterface<T> $items
     */
    public function __construct(
        private ResultSetInterface $items,
        private int $page,
        private int $perPage,
        private int $totalItems,
    ) {
        if ($this->page < 1) {
            throw new InvalidPageResultException(
                'Page result page must be greater than or equal to 1.',
            );
        }

        if ($this->perPage < 1) {
            throw new InvalidPageResultException(
                'Page result per-page value must be greater than or equal to 1.',
            );
        }

        if ($this->totalItems < 0) {
            throw new InvalidPageResultException(
                'Page result total items cannot be negative.',
            );
        }

        if ($this->items->count() > $this->perPage) {
            throw new InvalidPageResultException(
                'Page result item count cannot exceed per-page value.',
            );
        }
    }

    /**
     * @return ResultSetInterface<T>
     */
    public function items(): ResultSetInterface
    {
        return $this->items;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }

    public function totalPages(): int
    {
        if ($this->totalItems === 0) {
            return 0;
        }

        return (int) ceil($this->totalItems / $this->perPage);
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1 && $this->totalPages() > 0;
    }
}
