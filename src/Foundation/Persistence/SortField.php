<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Exceptions\InvalidSortFieldException;

final readonly class SortField
{
    public function __construct(
        private string $field,
        private SortDirection $direction = SortDirection::Ascending,
    ) {
        if (trim($this->field) === '') {
            throw new InvalidSortFieldException(
                'Sort field cannot be empty.',
            );
        }
    }

    public function field(): string
    {
        return $this->field;
    }

    public function direction(): SortDirection
    {
        return $this->direction;
    }
}
