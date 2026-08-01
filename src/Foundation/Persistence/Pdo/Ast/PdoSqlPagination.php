<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoQueryAstException;

final readonly class PdoSqlPagination
{
    public function __construct(
        private int $limit,
        private int $offset,
    ) {
        if ($this->limit < 1 || $this->offset < 0) {
            throw new InvalidPdoQueryAstException('SQL pagination requires a positive limit and a non-negative offset.');
        }
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function offset(): int
    {
        return $this->offset;
    }
}
