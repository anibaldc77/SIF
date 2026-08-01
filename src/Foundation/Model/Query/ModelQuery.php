<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Query;

use Sif\Foundation\Model\Exceptions\ModelQueryException;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Persistence\Pagination;
use Sif\Foundation\Persistence\Projection;
use Sif\Foundation\Persistence\Query;
use Sif\Foundation\Persistence\QueryCriterion;
use Sif\Foundation\Persistence\QueryOperator;
use Sif\Foundation\Persistence\SortDirection;
use Sif\Foundation\Persistence\SortField;

final readonly class ModelQuery
{
    public function __construct(
        private ModelMetadata $metadata,
        private Query $query = new Query(),
        private ModelSoftDeleteScope $softDeleteScope = ModelSoftDeleteScope::WithoutTrashed,
    ) {
    }

    public static function for(ModelMetadata $metadata): self
    {
        return new self($metadata);
    }

    public function metadata(): ModelMetadata
    {
        return $this->metadata;
    }

    public function persistenceQuery(): Query
    {
        $query = $this->query;
        $deletedAt = $this->metadata->deletedAt();
        if ($deletedAt === null || $this->softDeleteScope === ModelSoftDeleteScope::WithTrashed) {
            return $query;
        }

        return $query->withCriterion(new QueryCriterion(
            $deletedAt->value(),
            $this->softDeleteScope === ModelSoftDeleteScope::OnlyTrashed
                ? QueryOperator::IsNotNull
                : QueryOperator::IsNull,
        ));
    }

    public function where(string $attribute, QueryOperator $operator, mixed $value = null): self
    {
        $this->assertAttribute($attribute);

        return new self(
            $this->metadata,
            $this->query->withCriterion(new QueryCriterion($attribute, $operator, $value)),
            $this->softDeleteScope,
        );
    }

    public function orderBy(string $attribute, SortDirection $direction = SortDirection::Ascending): self
    {
        $this->assertAttribute($attribute);

        return new self(
            $this->metadata,
            $this->query->withSortField(new SortField($attribute, $direction)),
            $this->softDeleteScope,
        );
    }

    public function page(int $page, int $perPage): self
    {
        return new self(
            $this->metadata,
            $this->query->withPagination(new Pagination($page, $perPage)),
            $this->softDeleteScope,
        );
    }

    /** @param list<string> $attributes */
    public function select(array $attributes): self
    {
        foreach ($attributes as $attribute) {
            $this->assertAttribute($attribute);
        }

        return new self(
            $this->metadata,
            $this->query->withProjection(new Projection($attributes)),
            $this->softDeleteScope,
        );
    }

    public function withTrashed(): self
    {
        return new self($this->metadata, $this->query, ModelSoftDeleteScope::WithTrashed);
    }

    public function onlyTrashed(): self
    {
        if (!$this->metadata->usesSoftDeletes()) {
            throw new ModelQueryException('Only-trashed scope requires soft-delete metadata.');
        }

        return new self($this->metadata, $this->query, ModelSoftDeleteScope::OnlyTrashed);
    }

    public function softDeleteScope(): ModelSoftDeleteScope
    {
        return $this->softDeleteScope;
    }

    private function assertAttribute(string $attribute): void
    {
        if (!$this->metadata->hasAttribute($attribute)) {
            throw new ModelQueryException(sprintf('Unknown model query attribute "%s".', $attribute));
        }
    }
}
