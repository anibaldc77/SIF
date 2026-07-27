<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Contracts\QueryInterface;

final readonly class Query implements QueryInterface
{
    public function __construct(
        private QueryCriteria $criteria = new QueryCriteria(),
        private SortOrder $sortOrder = new SortOrder(),
        private ?Pagination $pagination = null,
        private Projection $projection = new Projection(),
    ) {
    }

    public function criteria(): QueryCriteria
    {
        return $this->criteria;
    }

    public function sortOrder(): SortOrder
    {
        return $this->sortOrder;
    }

    public function pagination(): ?Pagination
    {
        return $this->pagination;
    }

    public function projection(): Projection
    {
        return $this->projection;
    }

    public function withCriterion(QueryCriterion $criterion): self
    {
        return new self(
            criteria: $this->criteria->with($criterion),
            sortOrder: $this->sortOrder,
            pagination: $this->pagination,
            projection: $this->projection,
        );
    }

    public function withSortField(SortField $field): self
    {
        return new self(
            criteria: $this->criteria,
            sortOrder: $this->sortOrder->with($field),
            pagination: $this->pagination,
            projection: $this->projection,
        );
    }

    public function withPagination(?Pagination $pagination): self
    {
        return new self(
            criteria: $this->criteria,
            sortOrder: $this->sortOrder,
            pagination: $pagination,
            projection: $this->projection,
        );
    }

    public function withProjection(Projection $projection): self
    {
        return new self(
            criteria: $this->criteria,
            sortOrder: $this->sortOrder,
            pagination: $this->pagination,
            projection: $projection,
        );
    }
}
