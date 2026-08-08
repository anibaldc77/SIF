<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim;

use InvalidArgumentException;

final readonly class ScimSort
{
    public function __construct(
        private string $attributePath,
        private string $order = 'ascending'
    ) {
        if (!in_array($this->order, ['ascending', 'descending'], true)) {
            throw new InvalidArgumentException(
                'SCIM sort order is invalid.'
            );
        }
    }

    public function attributePath(): string
    {
        return $this->attributePath;
    }

    public function order(): string
    {
        return $this->order;
    }
}
