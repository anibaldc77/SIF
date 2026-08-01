<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final readonly class PdoSelectQuery
{
    /** @var list<PdoSqlSortTerm> */
    private array $sortTerms;

    /** @param iterable<PdoSqlSortTerm> $sortTerms */
    public function __construct(
        private PdoSqlIdentifier $source,
        private PdoSqlProjection $projection = new PdoSqlProjection(),
        private PdoSqlConjunction $criteria = new PdoSqlConjunction(),
        iterable $sortTerms = [],
        private ?PdoSqlPagination $pagination = null,
    ) {
        $this->sortTerms = [...$sortTerms];
    }

    public function source(): PdoSqlIdentifier
    {
        return $this->source;
    }

    public function projection(): PdoSqlProjection
    {
        return $this->projection;
    }

    public function criteria(): PdoSqlConjunction
    {
        return $this->criteria;
    }

    /** @return list<PdoSqlSortTerm> */
    public function sortTerms(): array
    {
        return $this->sortTerms;
    }

    public function pagination(): ?PdoSqlPagination
    {
        return $this->pagination;
    }

    public function parameters(): PdoSqlParameterBag
    {
        return $this->criteria->parameters();
    }
}
