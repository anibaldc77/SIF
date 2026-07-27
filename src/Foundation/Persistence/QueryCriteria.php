<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

final readonly class QueryCriteria
{
    /**
     * @var list<QueryCriterion>
     */
    private array $criteria;

    /**
     * @param list<QueryCriterion> $criteria
     */
    public function __construct(array $criteria = [])
    {
        $this->criteria = array_values($criteria);
    }

    /**
     * @return list<QueryCriterion>
     */
    public function all(): array
    {
        return $this->criteria;
    }

    public function isEmpty(): bool
    {
        return $this->criteria === [];
    }

    public function count(): int
    {
        return count($this->criteria);
    }

    public function with(QueryCriterion $criterion): self
    {
        return new self([...$this->criteria, $criterion]);
    }
}
