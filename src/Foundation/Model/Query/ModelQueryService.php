<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Query;

use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Exceptions\ModelQueryException;
use Sif\Foundation\Persistence\Pagination;

/** @template T of BaseModel */
final readonly class ModelQueryService
{
    /** @param ReadRepositoryInterface<T> $repository */
    public function __construct(private ReadRepositoryInterface $repository)
    {
    }

    /** @return list<T> */
    public function all(ModelQuery $query): array
    {
        $this->assertCompatible($query);
        $items = [];
        foreach ($this->repository->query($query->persistenceQuery())->all() as $item) {
            if (!$item instanceof BaseModel || $item::class !== $query->metadata()->modelClass()) {
                throw new ModelQueryException('Repository returned an incompatible model instance.');
            }

            /** @var T $item */
            $items[] = $item;
        }

        return $items;
    }

    /** @return T|null */
    public function first(ModelQuery $query): ?BaseModel
    {
        $items = $this->all($query->page(1, 1));

        return $items[0] ?? null;
    }

    /** @return ModelPage<T> */
    public function paginate(ModelQuery $query, int $page, int $perPage): ModelPage
    {
        $paged = $query->page($page, $perPage);

        return new ModelPage($this->all($paged), $page, $perPage);
    }

    private function assertCompatible(ModelQuery $query): void
    {
        if (
            $this->repository->name()->value() !== $query->metadata()->repositoryName()
            || $this->repository->managedType() !== $query->metadata()->modelClass()
        ) {
            throw new ModelQueryException('Repository is incompatible with model query metadata.');
        }
    }
}
