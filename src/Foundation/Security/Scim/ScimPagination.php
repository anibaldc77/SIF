<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim;

use InvalidArgumentException;

final readonly class ScimPagination
{
    public function __construct(
        private int $startIndex = 1,
        private int $count = 100
    ) {
        if ($this->startIndex < 1 || $this->count < 0) {
            throw new InvalidArgumentException(
                'SCIM pagination values are invalid.'
            );
        }
    }

    public function startIndex(): int
    {
        return $this->startIndex;
    }

    public function count(): int
    {
        return $this->count;
    }
}
