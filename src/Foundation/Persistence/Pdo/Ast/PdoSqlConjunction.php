<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final readonly class PdoSqlConjunction
{
    /** @var list<PdoSqlPredicate> */
    private array $predicates;

    /** @param iterable<PdoSqlPredicate> $predicates */
    public function __construct(iterable $predicates = [])
    {
        $values = [];
        foreach ($predicates as $predicate) {
            $values[] = $predicate;
        }
        $this->predicates = $values;
    }

    /** @return list<PdoSqlPredicate> */
    public function predicates(): array
    {
        return $this->predicates;
    }

    public function isEmpty(): bool
    {
        return $this->predicates === [];
    }

    public function parameters(): PdoSqlParameterBag
    {
        $parameters = [];
        foreach ($this->predicates as $predicate) {
            foreach ($predicate->parameters() as $parameter) {
                $parameters[] = $parameter;
            }
        }

        return new PdoSqlParameterBag($parameters);
    }
}
